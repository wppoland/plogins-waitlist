#!/usr/bin/env bash
# Prepare a clean WordPress.org trunk package for Restock, honouring .distignore.
# Bundles runtime Composer dependencies (wppoland/storefront-kit) into vendor/.
#
# Usage: ./scripts/prepare-wporg-release.sh [dist-dir]
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DIST_DIR="${1:-/tmp/restock-wporg-trunk}"

if ! command -v rsync > /dev/null 2>&1; then
    echo "rsync is required to prepare a WordPress.org release package." >&2
    exit 1
fi

echo "→ Installing production dependencies..."
composer install --no-dev --optimize-autoloader --working-dir="${ROOT_DIR}" --quiet

rm -rf "${DIST_DIR}"
mkdir -p "${DIST_DIR}"

rsync -a \
    --delete \
    --exclude-from="${ROOT_DIR}/.distignore" \
    --exclude '.git' --exclude 'node_modules' --exclude '.DS_Store' \
    "${ROOT_DIR}/" \
    "${DIST_DIR}/"

# Belt-and-braces: remove anything still matching a .distignore pattern.
while IFS= read -r pattern; do
    [[ -z "${pattern}" ]] && continue
    rm -rf "${DIST_DIR}/${pattern#/}"
done < "${ROOT_DIR}/.distignore"

# Purge bundled dependency dev artifacts.
if [[ -d "${DIST_DIR}/vendor" ]]; then
    find "${DIST_DIR}/vendor" -type d -name '.github' -prune -exec rm -rf {} +
    find "${DIST_DIR}/vendor" \( -name '.gitignore' -o -name 'phpstan.neon.dist' -o -name 'phpstan-baseline.neon' -o -name 'phpcs.xml.dist' \) -delete
fi

# Purge macOS artifacts.
find "${DIST_DIR}" -name '.DS_Store' -delete
find "${DIST_DIR}" -type d -empty -delete

echo "✓ Prepared WordPress.org trunk package in: ${DIST_DIR}"
echo "  Listing assets (icon/banner/screenshots) are synced separately by sync-wporg-svn.sh."
