#!/usr/bin/env bash

BASE_DIR="$(dirname -- "$(dirname -- "$(realpath "${BASH_SOURCE[0]}")")")";

cd "$BASE_DIR";

date_suffix="$(date +%Y%m%d)"
# NB: this will break if some developer starts using spaces in file names...
files="$(find . -name '*.php' -o -name '*.inc' | grep -v './vendor/' | sort | tr '\n' ' ')"

for filename in $files; do
	echo "Checking $filename ..."
	output="$(php -l "$filename" 2>&1)"
    if [ $? != 0 ]; then
    	echo "**ERROR** $output" >&2
    fi
done
