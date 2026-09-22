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

    def test_home_events_are_present_without_addresses(self):
        homes = {venue["id"]: venue for venue in self.archive["venues"] if venue["privateHome"]}
        self.assertEqual(set(homes), {13, 15})
        for home in homes.values():
            self.assertIsNone(home["street"])
            self.assertIsNone(home["postalCode"])
            self.assertIsNone(home["city"])
        self.assertEqual(sum(event["venueId"] in homes for event in self.archive["events"]), 3)

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
