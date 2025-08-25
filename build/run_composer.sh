#!/usr/bin/env bash

set -e

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
#composer --no-interaction validate

# abort if any files in vendor/ do not match upstream, ie. have been modified locally
composer --no-interaction status

# make it visible to developers when there are dependency upgrades available
composer --ignore-platform-reqs --no-interaction outdated

# generate an optimized autoload configuration
composer --ignore-platform-reqs --no-interaction dump-autoload --optimize --no-dev
