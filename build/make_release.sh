#!/usr/bin/env bash

set -e

help() {
	printf "Usage: make_release.sh [OPTIONS]

Script used to build a 'release': runs various syntax checks, makes sure composer setup is correct,
updates the translation files, creates database dumps and a tarball.

Options:
  -s \$ACTIONS  Skip executing one or more actions (separated by comma). Supported: check_php, composer, update_translations, dump_db, create_tarball

NB: some of the scripts used as part of the build process
"
}

SKIP_ACTIONS=

# parse cli options and arguments
while getopts ":hs:" opt
do
	case $opt in
		h)
			help
			exit 0
		;;
		s)
			SKIP_ACTIONS="$OPTARG"
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

if [ -n "$SKIP_ACTIONS" ]; then
	# remove whitespace, add commas encapsulating the string
	SKIP_ACTIONS=",${SKIP_ACTIONS// /},"
fi

echo "Building the release..."

if [[ "$SKIP_ACTIONS" != *",check_php,"* ]]; then
	echo "Checking syntax of php files..."

	"$BASE_DIR/build/check_syntax.sh" || exit 1
fi

#echo "Checking the sql files..."
#"$BASE_DIR/build/check_install_sql_files.sh"
#"$BASE_DIR/build/check_demo_db_dump.sh"

if [[ "$SKIP_ACTIONS" != *",composer,"* ]]; then
	echo "Checking and updating composer configuration..."

	"$BASE_DIR/build/run_composer.sh" || exit 1
fi

if [[ "$SKIP_ACTIONS" != *",update_translations,"* ]]; then
	echo "Updating translation files..."

	"$BASE_DIR/build/update_translations.sh" all
fi

if [[ "$SKIP_ACTIONS" != *",dump_db,"* ]]; then
	echo "Cleaning up and dumping the database..."

	# @todo review the options used - should we not add -t -d ?
	"$BASE_DIR/build/dump_database.sh" -o ./install/sql demo
fi

if [[ "$SKIP_ACTIONS" != *",create_tarball,"* ]]; then
	echo "Creating the final tarball..."

	"$BASE_DIR/build/create_tarball.sh"
fi

echo "Done"
