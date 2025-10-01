#!/usr/bin/env bash

set -e

help() {
	printf "Usage: setup_db.sh [OPTIONS]

Used to start a DB, set up appropriately.

Options:
  -P \$PORT
  -p \$ROOT_PASSWORD
  -t \$TYPE
  -v \$VERSION
"
}

DB_TYPE=mysql
DB_VERSION=native
DB_PORT=3306
DB_PASSWORD=root

# parse cli options and arguments
while getopts ":hP:p:t:v:" opt
do
	case $opt in
		h)
			help
			exit 0
			;;
		P)
			DB_PORT="$OPTARG"
			;;
		p)
			DB_PASSWORD="$OPTARG"
			;;
		t)
			DB_TYPE="$OPTARG"
			;;
		v)
			DB_VERSION="$OPTARG"
			;;
		\?)
			printf "\n\e[31mERROR: unknown option -${OPTARG}\e[0m\n\n" >&2
			help
			exit 1
			;;
	esac
done
shift $((OPTIND-1))

if [ -z "$DB_TYPE" ] || [ -z "$DB_VERSION" ] || [ -z "$DB_PORT" ] || [ -z "$DB_PASSWORD" ]; then
	echo "Please specify db type, version, port and password" >&2
	exit 1
fi

BASE_DIR="$(dirname -- "$(dirname -- "$(dirname -- "$(realpath "${BASH_SOURCE[0]}")")")")";

cd "$BASE_DIR";

if [ "$DB_VERSION" = native ]; then

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

	# @see https://github.com/actions/runner-images/blob/main/images/ubuntu/Ubuntu2204-Readme.md#mysql
	# @see https://github.com/actions/runner-images/blob/main/images/ubuntu/Ubuntu2404-Readme.md#mysql

	case "$DB_TYPE" in
		mysql)
			if [ "$DB_PORT" != 3306 ] || [ "$DB_PASSWORD" !=  root ]; then
				echo "Atm this script does not support custom db ports and passwords for native $DB_TYPE setups" >&2
				exit 1
			fi
			if [ -z "$GITHUB_ACTION" ]; then
				DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server
			fi
			sudo cp ./tests/setup/config/mysql/test.cnf /etc/mysql/conf.d/
			# Start the service
			sudo systemctl start mysql.service
		;;
		mariadb)
			if [ "$DB_PORT" != 3306 ] || [ "$DB_PASSWORD" !=  root ]; then
				echo "Atm this script does not support custom db ports and passwords for native $DB_TYPE setups" >&2
				exit 1
			fi
			if [ -z "$GITHUB_ACTION" ]; then
				DEBIAN_FRONTEND=noninteractive apt-get install -y mariadb-server
			fi
			sudo cp ./tests/setup/config/mariadb/test.cnf /etc/mysql/conf.d/
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

else

	case "$DB_TYPE" in
		mysql)
			docker run --rm --detach --name mysql -p "${DB_PORT}:3306" -e "MYSQL_ROOT_PASSWORD=$DB_PASSWORD" \
				-v "$BASE_DIR/tests/setup/config/mariadb/test.cnf:/etc/mysql/conf.d/test.cnf" \
				"${DB_TYPE}:${DB_VERSION}"
			# it seems that using --detach means we do not get an error exit code if the container aborts
			sleep 1
			if [ -z "$(docker ps --filter name=mysql -q)" ]; then
				echo "MySQL container failed starting up"
				exit 1
			fi
			# wait up to 10 secs for the db to be started up
			# @todo move to a function, to share code with mariadb
			COUNT=0
			ALIVE=no
			set +e
			while [ "$COUNT" -lt 60 ]; do
				docker exec mysql mysql -h127.0.0.1 -uroot -p"$DB_PASSWORD" -e 'show databases' >/dev/null 2>/dev/null
				if [ $? -eq 0 ]; then
					ALIVE=yes
					break
				fi
				echo "Waiting for mysql..."
				COUNT=$((COUNT+1))
				sleep 1
			done
			set -e
			if [ "$ALIVE" != yes ]; then
				echo "MySQL (in container) did not start up in time"
				echo "latest error:"
				docker exec mysql mysql -h127.0.0.1 -uroot -p"$DB_PASSWORD" -e 'show databases'
				exit 1
			fi
		;;
		mariadb)
			# the env var for root passwd changed across mariadb container image versions...
			docker run --rm --detach --name mariadb -p "${DB_PORT}:3306" -e "MARIADB_ROOT_PASSWORD=$DB_PASSWORD" -e "MYSQL_ROOT_PASSWORD=$DB_PASSWORD"\
				-v "$BASE_DIR/tests/setup/config/mariadb/test.cnf:/etc/mysql/conf.d/test.cnf" \
				"${DB_TYPE}:${DB_VERSION}"
			# it seems that using --detach means we do not get an error exit code if the container aborts
			sleep 1
			if [ -z "$(docker ps --filter name=mariadb -q)" ]; then
				echo "MariaDB container failed starting up"
				exit 1
			fi
			# wait up to 10 secs for the db to be started up
			COUNT=0
			ALIVE=no
			set +e
			while [ "$COUNT" -lt 60 ]; do
				docker exec mariadb mysql -h127.0.0.1 -uroot -p"$DB_PASSWORD" -e 'show databases' >/dev/null 2>/dev/null
				if [ $? -eq 0 ]; then
					# the db cli client changed name across mariadb container image versions...
					docker exec mariadb mariadb -h127.0.0.1 -uroot -p"$DB_PASSWORD" -e 'show databases' >/dev/null 2>/dev/null
				fi
				if [ $? -eq 0 ]; then
					ALIVE=yes
					break
				fi
				echo "Waiting for mariadb..."
				COUNT=$((COUNT+1))
				sleep 1
			done
			set -e
			if [ "$ALIVE" != yes ]; then
				echo "MariaDB (in container) did not start up in time"
				echo "latest error:"
				docker exec mariadb mysql -h127.0.0.1 -uroot -p"$DB_PASSWORD" -e 'show databases'
				docker exec mariadb mariadb -h127.0.0.1 -uroot -p"$DB_PASSWORD" -e 'show databases'
				exit 1
			fi
		;;
		\?)
			printf "\n\e[31mERROR: unsupported db type: $DB_TYPE\e[0m\n\n" >&2
			help
			exit 1
		;;
	esac
fi
