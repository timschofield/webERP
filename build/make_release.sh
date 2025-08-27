#!/usr/bin/env bash

set -e

help() {
	printf "Usage: make_release.sh

Legacy script used to build a 'release': updates the translation files, creates database dumps and a tarball
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

echo "Checking syntax of php files..."

"$BASE_DIR/build/check_syntax.sh" || exit 1

echo "Checking and updating composer configuration..."

"$BASE_DIR/build/run_composer.sh" || exit 1

echo "Updating translation files..."

"$BASE_DIR/build/update_translations.sh" all

echo "Cleaning up and dumping the database..."

# @todo review the options used - should we not add -t -d ?
"$BASE_DIR/build/dump_database.sh" -o ./install/sql demo

echo "Creating the final tarball..."

"$BASE_DIR/build/create_tarball.sh"
