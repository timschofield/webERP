#!/usr/bin/env bash

set -e

BASE_DIR="$(dirname -- "$(dirname -- "$(realpath "${BASH_SOURCE[0]}")")")";

# @todo grab this from a cli option (use getopts for parsing those)
OUTPUT_DIR="$HOME";

cd "$BASE_DIR";
current_dir="${PWD##*/}"
cd ..

if [ -f "$OUTPUT_DIR/webERP.zip" ]; then rm "$OUTPUT_DIR/webERP.zip"; fi

zip -r "$OUTPUT_DIR/webERP" "$current_dir" -x \*.git* \*/config.php \*build*
