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

    def test_private_tables_and_fields_are_absent(self):
        forbidden = {
            "password", "passwordsalt", "token", "tokenvalid", "mail", "mailconfirmed",
            "mailtoken", "guest", "guests", "history", "attendance",
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

    def test_user_profiles_have_only_public_fields(self):
        allowed = {"id", "name", "text", "website", "myspace", "facebook"}
        self.assertTrue(all(set(user) == allowed for user in self.archive["users"]))

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
        self.assertTrue((layout / "eventcorner.png").is_file())


if __name__ == "__main__":
    unittest.main()
