#!/usr/bin/env bash

set -e

BASE_DIR="$(dirname -- "$(dirname -- "$(dirname -- "$(realpath "${BASH_SOURCE[0]}")")")")";

cd "$BASE_DIR";

DB_TYPE="$1"
DB_VERSION="$2"

if [ -z "$DB_TYPE" ] || [ -z "$DB_VERSION" ]; then
	echo "Please specify db type as first arg and db version as 2nd" >&2
	exit 1
fi

if [ "$DB_VERSION" != native ]; then
	echo "At the moment only the db version native to the host is supported" >&2
	exit 1
fi

# Debugging
#echo '### /etc/mysql/'
#sudo ls -la /etc/mysql/
#echo
#echo '### /etc/mysql/debian-start'
#sudo cat /etc/mysql/debian-start
#echo
#echo '### /etc/mysql/debian.cnf'
#sudo cat /etc/mysql/debian.cnf
#echo
#echo '### /etc/mysql/my.cnf'
#sudo cat /etc/mysql/my.cnf
#echo
#echo '### /etc/mysql/my.cnf.fallback'
#sudo cat /etc/mysql/my.cnf.fallback
#echo
#echo '### /etc/mysql/mysql.cnf'
#sudo cat /etc/mysql/mysql.cnf
#echo
#sudo ls -la /etc/mysql/conf.d/
#echo
#sudo ls -la /etc/mysql/mysql.conf.d/

case "$DB_TYPE" in
	mysql)
		if [ -z "$GITHUB_ACTION" ]; then
			apt-get install mysql-server
		fi
		sudo copy ./tests/setup/config/mysql/test.cnf /etc/mysql/conf.d/
		# Start the service
		sudo systemctl start mysql.service
	;;
	mariadb)
		if [ -z "$GITHUB_ACTION" ]; then
			apt-get install mariadb-server
		fi
		sudo copy ./tests/setup/config/mariadb/test.cnf /etc/mysql/conf.d/
		# Start the service
		### @todo check the name of the service!
		sudo systemctl start mariadb.service
	;;
	\?)
		printf "\n\e[31mERROR: unsupported db type: $DB_TYPE\e[0m\n\n" >&2
		help
		exit 1
	;;
esac
