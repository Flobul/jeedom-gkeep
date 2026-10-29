#!/usr/bin/env bash
set -euo pipefail
# Never instantiate the plugin from its checkout: the manager writes under resources/data.
base="${GKEEP_TEST_SCRATCH:-${RUNNER_TEMP:-}}"
if [ -z "$base" ] || [ ! -d "$base" ]; then
  echo 'GKEEP_TEST_SCRATCH (local) or RUNNER_TEMP (CI) must name an existing directory' >&2
  exit 1
fi
root=$(mktemp -d "$base/gkeep-functional.XXXXXX")
trap 'rm -rf "$root"' EXIT
mkdir -p "$root/resources" "$root/core/class"
cp resources/gkeepmanager.py "$root/resources/gkeepmanager.py"
cp core/class/gkeep.class.php "$root/core/class/gkeep.class.php"
export GKEEP_TEST_ROOT="$root"
php .github/ci/functional/gkeep_runtime_test.php
python3 .github/ci/functional/test_gkeep_behavior.py
