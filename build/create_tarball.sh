#!/usr/bin/env bash

set -e

help() {
	printf "Usage: create_tarball.sh

Creates a zip file (aka. tarball) out of the local installation, saves it in the home dir of the current user
"
}

# parse cli options and arguments
while getopts ":h" opt
do
	case $opt in
		h)
			help
			exit 0
		;;
		\?)
			printf "\n\e[31mERROR: unknown option -${OPTARG}\e[0m\n\n" >&2
			help
			exit 1
		;;
	esac
done
shift $((OPTIND-1))

BASE_DIR="$(dirname -- "$(dirname -- "$(realpath "${BASH_SOURCE[0]}")")")";

# @todo grab this from a cli option
OUTPUT_DIR="$HOME";

cd "$BASE_DIR";
current_dir="${PWD##*/}"
cd ..

if [ -f "$OUTPUT_DIR/webERP.zip" ]; then rm "$OUTPUT_DIR/webERP.zip"; fi

zip -r "$OUTPUT_DIR/webERP" "$current_dir" -x \*.git* \*/config.php \*build*
