#!/usr/bin/env bash
#
# Build an upload-ready theme zip (a single top-level "wove/" folder), excluding
# development-only files. Produces ./wove.zip.
#
set -euo pipefail

SLUG="wove"
OUT="$(pwd)/${SLUG}.zip"
BUILD="$(mktemp -d)"
trap 'rm -rf "$BUILD"' EXIT

mkdir -p "$BUILD/$SLUG"
rsync -a \
	--exclude '.git' --exclude '.github' --exclude '.gitignore' \
	--exclude '.wp-env.json' --exclude 'node_modules' --exclude '.DS_Store' \
	--exclude '.playwright-mcp' --exclude '*.zip' \
	--exclude 'README.md' --exclude 'CONTRIBUTING.md' --exclude 'build.sh' \
	--exclude 'CODE_OF_CONDUCT.md' --exclude '.editorconfig' --exclude 'CHANGELOG.md' \
	./ "$BUILD/$SLUG/"

rm -f "$OUT"
( cd "$BUILD" && zip -r -X "$OUT" "$SLUG" -x '*.DS_Store' >/dev/null )

echo "Built $OUT"
