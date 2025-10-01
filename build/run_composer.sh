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

# @todo atm composer (2.8.11) generates warnings when run with php 8.5. We could check for both php version and composer
#       version and, if the bad combination is found, run composer with php deprecation errors silenced - use eg.
#       php -d error_level=... "$COMPOSER"
#COMPOSER_VERSION="$($COMPOSER --version 2>/dev/null | sed 's/Composer version//' | awk {'print $1}')"
#PHP_VERSION="$(php -r 'echo PHP_VERSION_ID;')"

# @todo enable this after we remove the comments from composer.json
#$COMPOSER --no-interaction validate
#
#echo

# make sure composer.lock is up-to-date wrt composer.json
$COMPOSER update --no-interaction --prefer-stable --prefer-dist  --no-autoloader --no-dev nothing

echo

if [ -d vendor/phpunit/phpunit ]; then
	echo "Warning: you have installed composer dev dependencies. Removing them..."
	$COMPOSER install --no-interaction --ignore-platform-reqs --prefer-dist --no-autoloader --no-dev
	# @todo we should checkout vendor/composer/installed.php, as we do in setup_dependencies.php - but that does not
	#       seem to work here ?
	#git checkout vendor/composer/installed.php
	echo
fi

# abort if any files in vendor/ do not match upstream, ie. have been modified locally
if [ -z "$GITHUB_ACTION" ]; then
	$COMPOSER status --no-interaction --verbose
else
	# @todo improve this hack - we found no better way to work around the fact that composer, run on github,
	#       does not find the git tag info from the installed phplot, and thus reports a version variation for it
	set +e
		DIFF="$($COMPOSER status --no-interaction 2>/dev/null | grep -v phplot/phplot)"
		if [ -n "$DIFF" ]; then
			echo "You have version variations in the following dependencies:" >&2
			echo "$DIFF" >&2
			echo "Use --verbose (-v) to see a list of files" >&2
			exit 1
		fi
	set -e
fi

echo

# make it visible to developers when there are dependency upgrades available
$COMPOSER outdated --no-interaction --ignore-platform-reqs

echo

# generate an optimized autoload configuration
$COMPOSER dump-autoload --no-interaction --ignore-platform-reqs --optimize --no-dev
