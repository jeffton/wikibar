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
    def test_parses_mysql_values(self):
        sql = "INSERT INTO `sample` (`id`, `name`, `note`) VALUES (1, 'Rock ''n'' roll', NULL), (2, 'Lin\\nje', 'x');"
        columns, rows = IMPORT_ARCHIVE.extract_insert(sql, "sample")
        self.assertEqual(columns, ["id", "name", "note"])
        self.assertEqual(rows, [[1, "Rock 'n' roll", None], [2, "Lin\nje", "x"]])


class PublicArchiveTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.archive_text = (ROOT / "public/data/archive.json").read_text(encoding="utf-8")
        cls.archive = json.loads(cls.archive_text)

    def test_expected_snapshot_counts(self):
        self.assertEqual(len(self.archive["events"]), 79)
        self.assertEqual(len(self.archive["bands"]), 112)
        self.assertEqual(len(self.archive["venues"]), 19)

    def test_private_tables_and_fields_are_absent(self):
        forbidden = {"user", "users", "guest", "guests", "password", "passwordsalt", "mail", "history"}

        def visit(value):
            if isinstance(value, dict):
                self.assertTrue(forbidden.isdisjoint(key.lower() for key in value))
                for nested in value.values():
                    visit(nested)
            elif isinstance(value, list):
                for nested in value:
                    visit(nested)

        visit(self.archive)

    def test_private_home_venues_are_absent(self):
        venue_names = {venue["name"] for venue in self.archive["venues"]}
        self.assertNotIn("KO & MO's lejlighed", venue_names)
        self.assertNotIn("Anna's lejlighed", venue_names)
        self.assertTrue(all(event["venueId"] not in {13, 15} for event in self.archive["events"]))

    def test_no_legacy_hashes_or_database_credentials(self):
        self.assertNotIn("passwordsalt", self.archive_text.lower())
        self.assertNotIn("mysql1035", self.archive_text.lower())
        self.assertNotRegex(self.archive_text, r"[a-f0-9]{96,}")


if __name__ == "__main__":
    unittest.main()
