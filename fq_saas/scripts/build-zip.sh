#!/usr/bin/env bash
# Builds a production-clean fq_saas-install.zip.
# Strips tests, dev docs, and vendor dev assets so Perfex ZIP installer stays lean.
#
# Usage: ./fq_saas/scripts/build-zip.sh [output_zip_path]

set -euo pipefail

MODULE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
MODULE_NAME="$(basename "$MODULE_DIR")"
OUT_ZIP="${1:-$(cd "$MODULE_DIR/.." && pwd)/${MODULE_NAME}-install.zip}"

STAGE_DIR="$(mktemp -d -t fq_saas_build_XXXXXX)"
trap 'rm -rf "$STAGE_DIR"' EXIT

echo "[build-zip] Staging into $STAGE_DIR"
rsync -a --delete \
    --exclude-from="$MODULE_DIR/.distignore" \
    "$MODULE_DIR/" "$STAGE_DIR/$MODULE_NAME/"

# Extra safety: hard delete anything that slipped through
find "$STAGE_DIR/$MODULE_NAME" \
    \( -name 'test.php' \
       -o -name 'phpunit.xml*' \
       -o -name '.phpunit.result.cache' \
       -o -name '.DS_Store' \
       -o -name 'CHANGELOG.md' \
    \) -type f -delete

find "$STAGE_DIR/$MODULE_NAME" -type d \
    \( -name 'tests' -o -name 'Tests' -o -name 'test' \) -prune -exec rm -rf {} +

if [[ -f "$OUT_ZIP" ]]; then
    rm -f "$OUT_ZIP"
fi

(cd "$STAGE_DIR" && zip -rq "$OUT_ZIP" "$MODULE_NAME")

SIZE=$(du -h "$OUT_ZIP" | cut -f1)
echo "[build-zip] Created $OUT_ZIP ($SIZE)"
