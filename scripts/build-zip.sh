#!/usr/bin/env bash
# Build a clean, installable ${NAME}.zip for local testing, honouring .distignore.
# Produces /tmp/${NAME}-build/restock and /tmp/${NAME}.zip.
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
# The package folder and zip must be named after the plugin slug, which is the
# Text Domain, not the local checkout directory and not the plugin's old name.
# This script still said "restock" long after the rename, so every local build
# produced a folder Plugin Check reads as a text-domain mismatch.
NAME="$(grep -m1 -oE 'Text Domain:[[:space:]]+[a-z0-9-]+' "$ROOT_DIR"/*.php | awk '{print $3}')"
[ -n "$NAME" ] || { echo "ERROR: could not read Text Domain from the plugin header" >&2; exit 1; }
OUT_DIR="${1:-/tmp/${NAME}-build}"
STAGE="${OUT_DIR}/${NAME}"

echo "→ Installing production dependencies..."
composer install --no-dev --optimize-autoloader --working-dir="${ROOT_DIR}" --quiet

rm -rf "${OUT_DIR}"
mkdir -p "${STAGE}"

# Copy everything except .distignore patterns.
rsync -a --exclude-from="${ROOT_DIR}/.distignore" \
    --exclude '.git' --exclude 'node_modules' \
    --exclude '.DS_Store' \
    "${ROOT_DIR}/" "${STAGE}/"

find "${STAGE}" -name '.DS_Store' -delete
if [[ -d "${STAGE}/vendor" ]]; then
    find "${STAGE}/vendor" -type d -name '.github' -prune -exec rm -rf {} +
    find "${STAGE}/vendor" \( -name '.gitignore' -o -name 'phpstan.neon.dist' -o -name 'phpstan-baseline.neon' -o -name 'phpcs.xml.dist' \) -delete
fi

rm -f /tmp/${NAME}.zip
( cd "${OUT_DIR}" && zip -rqX /tmp/${NAME}.zip "${NAME}" -x '*.DS_Store' )
echo "✓ Built /tmp/${NAME}.zip from ${STAGE}"
