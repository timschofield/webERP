#!/usr/bin/env bash

set -e

help() {
	printf "Usage: check_demo_db_dump.sh

Checks that the demo DB dump contains some required data
"
}

# @todo check the value of the FirstLogin line in `config` table

# @todo check that the db-version in the db is in sync with the files in sql/update

echo 'TO BE IMPLEMENTED!'

exit 1
