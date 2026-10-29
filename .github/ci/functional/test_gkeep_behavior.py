"""Offline regression checks. Sources are copied into GKEEP_TEST_ROOT by the runner."""
import importlib.util
import json
import io
import contextlib
import os
import sys
import types
import unittest
from pathlib import Path
from unittest.mock import patch

ROOT = Path(os.environ["GKEEP_TEST_ROOT"])
SCRIPT = ROOT / "resources" / "gkeepmanager.py"


class FakeKeep:
    def authenticate(self, username, token, device_id=None):
        self.credentials = (username, token, device_id)


class Blob:
    pass


class Note:
    id = "note123"
    timestamps = types.SimpleNamespace(edited=types.SimpleNamespace(timestamp=lambda: 1234))


class KeepWithMedia:
    def getMediaLink(self, blob):
        return "https://example.invalid/media"


class KeepTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        fake = types.ModuleType("gkeepapi")
        fake.Keep = FakeKeep
        with patch.dict(sys.modules, {"gkeepapi": fake}):
            spec = importlib.util.spec_from_file_location("gkeepmanager_ci", SCRIPT)
            cls.module = importlib.util.module_from_spec(spec)
            spec.loader.exec_module(cls.module)

    def test_request_stdin_not_required_on_command_line(self):
        observed = []
        def capture(manager, **kwargs):
            observed.append(manager.token)
        payload = {"args": ["--username", "user@example.test", "--token", "fixture-token", "get_notes"]}
        with patch.object(sys, "argv", [str(SCRIPT), "--request-stdin"]), patch.object(sys, "stdin", io.StringIO(json.dumps(payload))):
            with patch.object(self.module.GoogleKeepManager, "get_notes", new=capture):
                self.module.main()
        self.assertEqual(["fixture-token"], observed)

    def test_blobs_have_distinct_paths_and_download_failure_is_nonfatal(self):
        manager = self.module.GoogleKeepManager("user@example.test", "fixture-token")
        blobs = [Blob(), Blob()]
        paths = []
        def download(url, destination):
            paths.append(destination)
            if len(paths) == 1:
                raise OSError("synthetic failure")
            Path(destination).write_bytes(b"\x89PNG\r\n\x1a\n")
        with patch.object(self.module.urllib.request, "urlretrieve", side_effect=download):
            with self.assertLogs(level="WARNING") as logs:
                result = manager.process_blobs(blobs, KeepWithMedia(), Note())
        self.assertEqual(2, len(paths))
        self.assertNotEqual(paths[0], paths[1])
        self.assertEqual(1, len(result))
        self.assertTrue(result[0]["file"].endswith(".png"))
        self.assertTrue(Path(result[0]["file"]).exists())
        self.assertNotIn("https://", " ".join(logs.output))

    def test_remote_note_id_cannot_escape_attachment_directory(self):
        manager = self.module.GoogleKeepManager("user@example.test", "fixture-token")
        note = types.SimpleNamespace(id="../../outside", timestamps=Note.timestamps)
        with patch.object(self.module.urllib.request, "urlretrieve", side_effect=lambda url, dst: Path(dst).write_bytes(b"\x89PNG\r\n\x1a\n")):
            result = manager.process_blobs([Blob()], KeepWithMedia(), note)
        self.assertEqual(1, len(result))
        self.assertEqual(Path(result[0]["file"]).parent, Path(manager.path))

    def test_bulk_cron_does_not_download_blobs_of_disabled_notes(self):
        stamp = types.SimpleNamespace(timestamp=lambda: 1234)
        notes = []
        for note_id in ("enabled", "disabled"):
            notes.append(types.SimpleNamespace(
                id=note_id, type=types.SimpleNamespace(name="Note"), title=note_id,
                text="", sort=0, color=types.SimpleNamespace(value="DEFAULT"),
                archived=False, pinned=False, trashed=False,
                timestamps=types.SimpleNamespace(created=stamp, edited=stamp, updated=stamp),
                collaborators=types.SimpleNamespace(all=lambda: []),
                annotations=types.SimpleNamespace(all=lambda: []), blobs=[Blob()]
            ))
        class KeepWithNotes(FakeKeep):
            def all(self): return notes
            def sync(self): pass
        visited = []
        with patch.object(self.module.gkeepapi, "Keep", KeepWithNotes), patch.object(self.module.gkeepapi, "node", types.SimpleNamespace(List=type("List", (), {})), create=True):
            with patch.object(self.module.GoogleKeepManager, "process_blobs", new=lambda manager, blobs, keep, note: visited.append(note.id) or []):
                with contextlib.redirect_stdout(io.StringIO()) as output:
                    self.module.GoogleKeepManager("user@example.test", "fixture-token").get_notes(blob_note_ids=["enabled"])
        self.assertEqual(["enabled"], visited)
        self.assertEqual(2, len(json.loads(output.getvalue())["result"]))

    def test_sync_never_automatically_deletes_equipment_on_partial_snapshots(self):
        php = (ROOT / "core" / "class" / "gkeep.class.php").read_text()
        self.assertNotIn("self::removeOldEqlogic(", php)

    def test_empty_lists_still_take_the_list_path(self):
        php = (ROOT / "core" / "class" / "gkeep.class.php").read_text()
        self.assertIn("if (isset($_note['list']) && is_array($_note['list'])) {", php)

    def test_custom_widget_name_is_escaped_before_template_replacement(self):
        php = (ROOT / "core" / "class" / "gkeep.class.php").read_text()
        self.assertIn("$replace['#name_display#'] = htmlspecialchars(", php)


if __name__ == "__main__":
    unittest.main()
