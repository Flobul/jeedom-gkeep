#!/usr/bin/env bash
set -euo pipefail
# Repository-specific checks migrated from previous workflows.
echo "Plugin-specific checks: not applicable"
python3 .github/ci/test_release_guard.py
python3 .github/ci/test_release_workflow.py
