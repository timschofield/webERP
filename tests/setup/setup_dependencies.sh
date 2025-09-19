#!/usr/bin/env bash

# @todo allow a '--uninstall' action

help() {
	printf "Usage: setup_dependencies.sh [OPTIONS]

Used to install / uninstall the php dependencies used for testing.

Options:
  -u    uninstall
"
}

ACTION=install

# parse cli options and arguments
while getopts ":hu" opt
do
	case $opt in
		h)
			help
			exit 0
			;;
		u)
			ACTION=uninstall
			;;
		\?)
			printf "\n\e[31mERROR: unknown option -${OPTARG}\e[0m\n\n" >&2
			help
			exit 1
			;;
	esac
done
shift $((OPTIND-1))

# Allow users to define the COMPOSER env var before running this script if they use the composer binary outside of PATH
# or with a different name
COMPOSER=${COMPOSER:-composer}

case $ACTION in
	uninstall)
		$COMPOSER install --no-interaction --prefer-dist --ignore-platform-reqs --optimize-autoloader --no-dev
		# This file will most likely have been modified during the install phase, and is not rolled back by the command above
		git checkout vendor/composer/installed.php
		;;
	install)
		# @todo is there a composer command which does require us to know which packages to force-install?
        $COMPOSER update --no-interaction --prefer-stable --prefer-dist phpunit/phpunit symfony/browser-kit symfony/css-selector symfony/http-client symfony/mime
		;;
esac
