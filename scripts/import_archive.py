#!/usr/bin/env python3
"""Create the public, privacy-scrubbed Wikibar archive from the 2007 SQL dump."""

from __future__ import annotations

import argparse
import json
import re
from pathlib import Path

PUBLIC_TABLES = {"appearance", "band_version", "current", "event", "venue_version"}
PRIVATE_VENUE_IDS = {13, 15}  # Private apartments in the original data.


def extract_insert(sql: str, table: str) -> tuple[list[str], list[list[object]]]:
    marker = f"INSERT INTO `{table}`"
    start = sql.find(marker)
    if start < 0:
        return [], []

    columns_start = sql.index("(", start)
    columns_end = sql.index(") VALUES", columns_start)
    columns = re.findall(r"`([^`]+)`", sql[columns_start:columns_end])
    values_start = columns_end + len(") VALUES")

    values_end = values_start
    quoted = False
    while values_end < len(sql):
        char = sql[values_end]
        if char == "'":
            if quoted and values_end + 1 < len(sql) and sql[values_end + 1] == "'":
                values_end += 2
                continue
            quoted = not quoted
        elif char == ";" and not quoted:
            break
        values_end += 1

    return columns, parse_tuples(sql[values_start:values_end])


def parse_tuples(source: str) -> list[list[object]]:
    rows: list[list[object]] = []
    row: list[object] | None = None
    token: list[str] = []
    quoted = False
    was_quoted = False
    index = 0

    def finish_value() -> None:
        nonlocal token, was_quoted
        assert row is not None
        raw = "".join(token).strip()
        if was_quoted:
            value: object = raw
        elif raw.upper() == "NULL":
            value = None
        elif re.fullmatch(r"-?\d+", raw):
            value = int(raw)
        else:
            value = raw
        row.append(value)
        token = []
        was_quoted = False

    while index < len(source):
        char = source[index]
        if quoted:
            if char == "'":
                if index + 1 < len(source) and source[index + 1] == "'":
                    token.append("'")
                    index += 2
                    continue
                quoted = False
            elif char == "\\" and index + 1 < len(source):
                escapes = {"n": "\n", "r": "\r", "t": "\t", "0": "\0"}
                index += 1
                token.append(escapes.get(source[index], source[index]))
            else:
                token.append(char)
        elif char == "(":
            row = []
        elif char == "'" and row is not None:
            quoted = True
            was_quoted = True
        elif char == "," and row is not None:
            finish_value()
        elif char == ")" and row is not None:
            finish_value()
            rows.append(row)
            row = None
        elif row is not None:
            token.append(char)
        index += 1

    return rows


def records(sql: str, table: str) -> list[dict[str, object]]:
    columns, rows = extract_insert(sql, table)
    return [dict(zip(columns, row, strict=True)) for row in rows]


def clean_url(value: object) -> str | None:
    if not value:
        return None
    url = str(value).strip()
    if url.startswith(("http://", "https://")):
        return url
    return f"https://{url}"


def build_archive(sql: str) -> dict[str, object]:
    current = {
        (row["type"], row["id"]): row["version"]
        for row in records(sql, "current")
        if row["type"] in {"band", "venue"}
    }

    bands = []
    for row in records(sql, "band_version"):
        if current.get(("band", row["id"])) != row["version"]:
            continue
        bands.append({
            "id": row["id"], "name": row["name"], "sortName": row["name_sort"],
            "country": row["country"], "text": row["text"],
            "website": clean_url(row["url_website"]),
            "myspace": clean_url(f"myspace.com/{row['url_myspace']}") if row["url_myspace"] else None,
        })

    venues = []
    for row in records(sql, "venue_version"):
        if row["id"] in PRIVATE_VENUE_IDS or current.get(("venue", row["id"])) != row["version"]:
            continue
        venues.append({
            "id": row["id"], "name": row["name"], "sortName": row["name_sort"],
            "street": row["address_street"], "postalCode": row["address_postalcode"],
            "city": row["address_city"], "country": row["address_country"],
            "typicalEntry": row["price_entry"], "bottle": row["price_bottle"],
            "draught": row["price_draught"], "shot": row["price_shot"],
            "drink": row["price_drink"], "wardrobe": row["wardrobe"],
            "wardrobePrice": row["wardrobe_price"], "musicStarts": row["music_starts_at"],
            "website": clean_url(row["url_website"]),
            "myspace": clean_url(f"myspace.com/{row['url_myspace']}") if row["url_myspace"] else None,
            "text": row["text"],
        })

    events = []
    allowed_event_ids: set[int] = set()
    for row in records(sql, "event"):
        if row["venueID"] in PRIVATE_VENUE_IDS:
            continue
        allowed_event_ids.add(int(row["id"]))
        events.append({
            "id": row["id"], "type": row["eventtype"], "name": row["name"],
            "venueId": row["venueID"], "date": row["date"], "time": row["time"],
            "endDate": row["enddate"], "price": row["price"], "text": row["text"],
            "website": clean_url(row["url_website"]),
            "myspace": clean_url(f"myspace.com/{row['url_myspace']}") if row["url_myspace"] else None,
            "status": row["status"],
        })

    appearances = [
        {"eventId": row["eventID"], "bandId": row["bandID"], "sequence": row["sequence"]}
        for row in records(sql, "appearance") if row["eventID"] in allowed_event_ids
    ]

    return {
        "meta": {
            "snapshot": "2007-08-28",
            "privacy": "Accounts, credentials, attendance, edit history and private-home events are excluded.",
        },
        "bands": sorted(bands, key=lambda row: str(row["sortName"]).casefold()),
        "venues": sorted(venues, key=lambda row: str(row["sortName"]).casefold()),
        "events": sorted(events, key=lambda row: (str(row["date"]), int(row["id"]))),
        "appearances": appearances,
    }


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("source", type=Path, help="Path to the private 2007 MySQL dump")
    parser.add_argument("output", type=Path, nargs="?", default=Path("public/data/archive.json"))
    args = parser.parse_args()

    archive = build_archive(args.source.read_text(encoding="utf-8-sig"))
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(archive, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(
        f"Wrote {len(archive['events'])} events, {len(archive['bands'])} bands and "
        f"{len(archive['venues'])} venues to {args.output}"
    )


if __name__ == "__main__":
    main()
