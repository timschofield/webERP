#!/usr/bin/env bash

set -e

# Allow users to define the COMPOSER env var before running this script if they use the composer binary outside of PATH
# or with a different name
COMPOSER=${COMPOSER:-composer}

help() {
	printf "Usage: run_composer.sh

Checks that the dependencies in the vendor folder do match the composer.lock file, and generates an optimized autoloader
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

cd "$BASE_DIR";

# @todo enable this after we remove the comments from composer.json
#$COMPOSER --no-interaction validate

# make sure composer.lock is up-to-date wrt composer.json
$COMPOSER update --no-dev --no-autoloader --no-interaction nothing

if [ -d vendor/phpunit/phpunit ]; then
	echo "Warning: you have installed composer dev dependencies. Removing them..."
	$COMPOSER install --ignore-platform-reqs --no-interaction --no-dev
fi

# abort if any files in vendor/ do not match upstream, ie. have been modified locally
$COMPOSER status --no-interaction

# make it visible to developers when there are dependency upgrades available
$COMPOSER outdated --ignore-platform-reqs --no-interaction

# generate an optimized autoload configuration
$COMPOSER dump-autoload --ignore-platform-reqs --no-interaction --optimize --no-dev
