import hashlib
import importlib.util
import json
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SPEC = importlib.util.spec_from_file_location("import_archive", ROOT / "scripts/import_archive.py")
IMPORT_ARCHIVE = importlib.util.module_from_spec(SPEC)
assert SPEC.loader
SPEC.loader.exec_module(IMPORT_ARCHIVE)


class SqlParserTests(unittest.TestCase):
    def test_parses_mysql_values_across_multiple_inserts(self):
        sql = """INSERT INTO `sample` (`id`, `name`, `note`) VALUES (1, 'Rock ''n'' roll', NULL);
INSERT INTO `sample` (`id`, `name`, `note`) VALUES (2, 'Lin\\nje', 'x'), (3, 'It\\'s; okay', NULL);"""
        columns, rows = IMPORT_ARCHIVE.extract_insert(sql, "sample")
        self.assertEqual(columns, ["id", "name", "note"])
        self.assertEqual(rows, [[1, "Rock 'n' roll", None], [2, "Lin\nje", "x"], [3, "It's; okay", None]])


class LegacySourceTests(unittest.TestCase):
    def test_original_source_snapshots_are_present_without_private_artifacts(self):
        legacy = ROOT / "legacy"
        self.assertTrue((legacy / "2007-05-18/wikibar.dk/site/index.php").is_file())
        self.assertTrue((legacy / "2007-05-18/wikibar.dk/classes/database.php").is_file())
        self.assertTrue((legacy / "2016-12-08-partial/classes/eventapi.php").is_file())
        self.assertTrue((legacy / "2016-12-08-partial/mobile/index.php").is_file())
        self.assertFalse(any(legacy.rglob("*.sql")))
        self.assertFalse((legacy / "2007-05-18/wikibar.dk/config.php").exists())
        self.assertFalse((legacy / "2016-12-08-partial/config.php").exists())
        self.assertFalse(any(path.name == "templates_c" for path in legacy.rglob("templates_c")))

        manifest_path = legacy / "MANIFEST.sha256"
        expected = {}
        for line in manifest_path.read_text(encoding="utf-8").splitlines():
            digest, path = line.split("  ", 1)
            expected[path] = digest
        actual = {
            str(path.relative_to(legacy)): hashlib.sha256(path.read_bytes()).hexdigest()
            for path in legacy.rglob("*")
            if path.is_file() and path != manifest_path
        }
        self.assertEqual(actual, expected)


class StaticAppTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.source = (ROOT / "public/app.js").read_text(encoding="utf-8")
        cls.archive = json.loads((ROOT / "public/data/archive.json").read_text(encoding="utf-8"))

    def test_profile_list_is_compact_and_links_to_calendar_event(self):
        self.assertEqual(self.source.count("events.slice(-6).reverse()"), 2)
        self.assertIn('href: `?view=calendar&page=${page}#event-${event.id}`', self.source)
        self.assertIn('card.id = `event-${event.id}`', self.source)
        self.assertIn('location.hash === `#event-${event.id}`', self.source)
        self.assertIn('scrollIntoView({ block: "center" })', self.source)

    def test_facebook_links_use_the_original_accessible_icon(self):
        self.assertEqual(self.source.count('"linkicon-facebook.png", "Facebook", 18, 18'), 2)
        self.assertIn("icon.alt = label", self.source)
        self.assertIn("icon.width = width", self.source)
        self.assertIn("icon.height = height", self.source)

    def test_known_profile_event_resolves_to_its_calendar_page(self):
        event_index = next(index for index, event in enumerate(self.archive["events"]) if event["id"] == 362)
        self.assertEqual(event_index // 12 + 1, 31)

    def test_retro_browse_and_statistics_ui_is_present(self):
        self.assertIn("function browseRanges(items, perPage)", self.source)
        self.assertIn("pages[0].first = [...pages[0].first][0]", self.source)
        self.assertIn("pages.at(-1).last = [...pages.at(-1).last][0]", self.source)
        self.assertIn("const perPage = 30", self.source)
        self.assertIn(".slice(0, perPage)", self.source)
        self.assertIn('pageLink.setAttribute("aria-current", "page")', self.source)
        self.assertIn('rankingTable("Top 5 bands"', self.source)
        self.assertIn('rankingTable("Top 5 spillesteder"', self.source)
        self.assertIn('rankingTable("Top 5 brugere"', self.source)
        self.assertIn('text: "Største events"', self.source)
        self.assertIn('text: "Senest oprettet"', self.source)


class PublicArchiveTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.archive_text = (ROOT / "public/data/archive.json").read_text(encoding="utf-8")
        cls.archive = json.loads(cls.archive_text)

    def test_expected_snapshot_counts(self):
        self.assertEqual(len(self.archive["events"]), 363)
        self.assertEqual(len(self.archive["bands"]), 388)
        self.assertEqual(len(self.archive["venues"]), 50)
        self.assertEqual(len(self.archive["users"]), 21)
        self.assertEqual(len(self.archive["appearances"]), 562)
        self.assertEqual(len(self.archive["attendance"]), 245)

    def test_events_use_the_original_calendar_order(self):
        events = self.archive["events"]
        expected = sorted(events, key=lambda event: (
            event["date"], event["time"] or "", event["endDate"] or "", event["id"],
        ))
        self.assertEqual(events, expected)

    def test_public_statistics_match_the_legacy_formulas(self):
        event_by_id = {event["id"]: event for event in self.archive["events"]}
        attendance = {}
        for entry in self.archive["attendance"]:
            attendance[entry["eventId"]] = attendance.get(entry["eventId"], 0) + 1

        band_scores = {}
        band_latest = {}
        for appearance in self.archive["appearances"]:
            event = event_by_id[appearance["eventId"]]
            count = attendance.get(event["id"], 0)
            if count:
                band_id = appearance["bandId"]
                band_scores[band_id] = band_scores.get(band_id, 0) + count
                band_latest[band_id] = max(band_latest.get(band_id, ""), event["date"])
        bands = {band["id"]: band["name"] for band in self.archive["bands"]}
        top_bands = sorted(band_scores, key=lambda band_id: (-band_scores[band_id], -int(band_latest[band_id].replace("-", "")), -band_id))[:5]
        self.assertEqual(
            [(bands[band_id], band_scores[band_id]) for band_id in top_bands],
            [("Snake and Jet's Amazing Bullit Band", 17), ("Decorate.Decorate", 17),
             ("Beta Satan", 15), ("The City Kill", 12), ("Sha La Las", 11)],
        )

        top_events = sorted(
            attendance,
            key=lambda event_id: (
                -attendance[event_id], -int(event_by_id[event_id]["date"].replace("-", "")), -event_id,
            ),
        )[:5]
        self.assertEqual(
            [(event_by_id[event_id]["name"], attendance[event_id]) for event_id in top_events],
            [("Roskilde Festival 2007", 8), ("Nakkefestival 2007", 5),
             ("Vesterbro Festival", 5), ("Roskilde Festival 2009", 4),
             ("Roskilde Festival 2008", 4)],
        )

        venue_scores = {}
        venue_latest = {}
        for event in self.archive["events"]:
            count = attendance.get(event["id"], 0)
            if count and event["venueId"]:
                venue_id = event["venueId"]
                venue_scores[venue_id] = venue_scores.get(venue_id, 0) + count
                venue_latest[venue_id] = max(venue_latest.get(venue_id, ""), event["date"])
        venues = {venue["id"]: venue["name"] for venue in self.archive["venues"]}
        top_venues = sorted(venue_scores, key=lambda venue_id: (
            -venue_scores[venue_id], -int(venue_latest[venue_id].replace("-", "")), -venue_id,
        ))[:5]
        self.assertEqual(
            [(venues[venue_id], venue_scores[venue_id]) for venue_id in top_venues],
            [("Loppen", 33), ("Vega", 23), ("Elværket", 14), ("Stengade", 11), ("Lades Kælder", 9)],
        )

        user_scores = {}
        for entry in self.archive["attendance"]:
            user_scores[entry["userId"]] = user_scores.get(entry["userId"], 0) + 1
        users = {user["id"]: user["name"] for user in self.archive["users"]}
        self.assertEqual(
            [(users[user_id], user_scores[user_id]) for user_id in sorted(user_scores, key=user_scores.get, reverse=True)[:5]],
            [("Jeff", 91), ("Mo", 69), ("KO", 41), ("anne", 24), ("Anette 13.2", 4)],
        )

    def test_browse_page_ranges_match_the_legacy_boundaries(self):
        self.assertEqual((len(self.archive["bands"]) + 29) // 30, 13)
        self.assertEqual((len(self.archive["venues"]) + 29) // 30, 2)
        bands = self.archive["bands"]
        page_edges = [
            (bands[index]["sortName"], bands[min(index + 29, len(bands) - 1)]["sortName"])
            for index in range(0, len(bands), 30)
        ]
        self.assertEqual(page_edges[0], ("1234", "Bob Log III"))
        self.assertEqual(page_edges[-1], ("Twins Twins", "Zombies, The"))

    def test_private_tables_and_fields_are_absent(self):
        forbidden = {
            "password", "passwordsalt", "token", "tokenvalid", "mail", "mailconfirmed",
            "mailtoken", "guest", "guests", "history",
        }

        def visit(value):
            if isinstance(value, dict):
                self.assertTrue(forbidden.isdisjoint(key.lower() for key in value))
                for nested in value.values():
                    visit(nested)
            elif isinstance(value, list):
                for nested in value:
                    visit(nested)

        visit(self.archive)

    def test_user_profiles_and_attendance_have_only_public_fields(self):
        allowed = {"id", "name", "text", "website", "myspace", "facebook"}
        self.assertTrue(all(set(user) == allowed for user in self.archive["users"]))
        self.assertTrue(all(set(entry) == {"eventId", "userId"} for entry in self.archive["attendance"]))
        event_ids = {event["id"] for event in self.archive["events"]}
        user_ids = {user["id"] for user in self.archive["users"]}
        self.assertTrue(all(entry["eventId"] in event_ids for entry in self.archive["attendance"]))
        self.assertTrue(all(entry["userId"] in user_ids for entry in self.archive["attendance"]))

    def test_home_events_and_source_null_addresses_are_preserved(self):
        homes = {venue["id"]: venue for venue in self.archive["venues"] if venue["id"] in {13, 15}}
        self.assertEqual({venue["name"] for venue in homes.values()}, {"KO & MO's lejlighed", "Anna's lejlighed"})
        for home in homes.values():
            self.assertNotIn("privateHome", home)
            self.assertIsNone(home["street"])
            self.assertIsNone(home["postalCode"])
            self.assertIsNone(home["city"])
            self.assertIsNone(home["country"])
        self.assertEqual(sum(event["venueId"] in homes for event in self.archive["events"]), 3)

    def test_raw_source_urls_are_preserved(self):
        urls = [
            row[field]
            for collection in ("bands", "venues", "events", "users")
            for row in self.archive[collection]
            for field in ("website", "myspace", "facebook")
            if row[field]
        ]
        self.assertEqual(len(urls), 287)
        self.assertTrue(all("://" not in value for value in urls))
        decorate = next(band for band in self.archive["bands"] if band["id"] == 1)
        self.assertEqual(decorate["website"], "decoratedecorate.com")
        self.assertEqual(decorate["myspace"], "decoratedecorate")
        self.assertNotIn("dataThrough", self.archive["meta"])

    def test_no_legacy_hashes_or_database_credentials(self):
        self.assertNotIn("passwordsalt", self.archive_text.lower())
        self.assertNotIn("mysql1035", self.archive_text.lower())
        self.assertNotRegex(self.archive_text, r"[a-f0-9]{96,}")

    def test_original_event_artwork_is_present(self):
        layout = ROOT / "public/layout"
        for event_type in ("concert", "festival", "party", "release", "releaseparty"):
            self.assertTrue((layout / f"event-{event_type}.png").is_file())
            self.assertTrue((layout / f"eventsmall-{event_type}.png").is_file())
        self.assertTrue((layout / "eventcorner.png").is_file())
        facebook_icon = layout / "linkicon-facebook.png"
        self.assertTrue(facebook_icon.is_file())
        self.assertEqual(
            hashlib.sha256(facebook_icon.read_bytes()).hexdigest(),
            "18956b7dcedd7732226d8e4a09e94635d01bfa94e2b09b213afd0f2dcd868a12",
        )


if __name__ == "__main__":
    unittest.main()
