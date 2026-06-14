#!/usr/bin/env bash
# Build a clean, installable restock.zip for local testing, honouring .distignore.
# Produces /tmp/restock-build/restock and /tmp/restock.zip.
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUT_DIR="${1:-/tmp/restock-build}"
STAGE="${OUT_DIR}/restock"

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

rm -f /tmp/restock.zip
( cd "${OUT_DIR}" && zip -rqX /tmp/restock.zip restock -x '*.DS_Store' )
echo "✓ Built /tmp/restock.zip from ${STAGE}"
