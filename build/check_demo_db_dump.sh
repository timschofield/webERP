#!/usr/bin/env bash

set -e

help() {
	printf "Usage: check_demo_db_dump.sh

Checks that the demo DB dump contains some required data and no unexpected data
"
}

BASE_DIR="$(dirname -- "$(dirname -- "$(realpath "${BASH_SOURCE[0]}")")")";

cd "$BASE_DIR";

# check the value of the FirstLogin line in `config` table - it should be either missing, or not be 0
if fgrep -q "('FirstLogin','1')" ./install/sql/demo.sql; then
	echo "Demo sql file contains wrong value for FirstLogin config" >&2
	exit 1
fi

# check that all tables listed in demo.sql `insert into` do exist in install/sql/tables
DEMO_TABLES="$(grep -i 'insert into' ./install/sql/demo.sql | tr '`' ' ' | sed -r 's/INSERT +INTO +//g' | awk '{print $1}' | uniq | sort | tr "\n" ' ')"
for TABLE in $DEMO_TABLES; do
	if [ ! -f "install/sql/tables/${TABLE}.sql" ]; then
		echo "Table referenced in demo sql file is not in list of db tables" >&2
		exit 1
	fi
done

# @todo check that the db-version in the db is in sync with the files in sql/update
