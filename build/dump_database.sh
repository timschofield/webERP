#!/usr/bin/env bash

set -e

# @todo allow to optionally add drop-table statements just before create-table ones using --skip-add-drop-table/--add-drop-table
# @todo review the options used for the mysqldump command. Can we do better than using --skip-opt?
#       nb: --skip-opt disables --add-drop-table, --add-locks, --create-options, --quick, --extended-insert,
#       --lock-tables, --set-charset, and --disable-keys

help() {
	printf "Usage: dump_database.sh [OPIONS] ACTION

Used to create
a) an 'empty' database to use when creating a new company: default.sql, and
b) a 'demo' database containing operational data for learning and testing: demo.sql.

Action: either default, demo or all

Options:
  -c        adds 'create schema' to the sql scripts it creates
  -t        adds 'create tables' to the sql scripts it creates
  -d \$DIR   use a custom directory for saving the files to

Uses env vars: MYSQL_HOST, MYSQL_PORT, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE

NB: the db user must have sufficient permissions to be able to execute mysqldump. You might need to use the admin
user instead of the user used to run the application.

NB: truncates table audittrail on the live db in use.
"
}

ADD_CREATE_SCHEMA_STATEMENTS=false
ADD_CREATE_TABLES_STATEMENTS=false

# parse cli options and arguments
while getopts ":cd:ht" opt
do
	case $opt in
		c)
			ADD_CREATE_SCHEMA_STATEMENTS=true
		;;
        d)
        	TARGET_DIR="$OPTARG"
		;;
		h)
			help
			exit 0
		;;
		t)
			ADD_CREATE_TABLES_STATEMENTS=true
		;;
		\?)
			printf "\n\e[31mERROR: unknown option -${OPTARG}\e[0m\n\n" >&2
			help
			exit 1
		;;
	esac
done
shift $((OPTIND-1))

MYSQL_USER="${MYSQL_USER:-root}";
MYSQL_PASSWORD="${MYSQL_PASSWORD:-root}";
MYSQL_DATABASE="${MYSQL_DATABASE:-weberpdemo}"
MYSQL_HOST="${MYSQL_HOST:-localhost}"
MYSQL_PORT="${MYSQL_PORT:-3306}"

BASE_DIR="$(dirname -- "$(dirname -- "$(realpath "${BASH_SOURCE[0]}")")")";
if [ -z "$TARGET_DIR" ]; then
	TARGET_DIR="$BASE_DIR/sql/mysql"
fi

ACTION="$1"
if [ "$ACTION" != all ] && [ "$ACTION" != default ] && [ "$ACTION" != demo ]; then
	echo "ERROR: please provide an argument. It must be either 'all', 'default' or 'demo'" >&2
	exit 1
fi

mysql -u"$MYSQL_USER"  -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" < "$BASE_DIR/build/TruncateAuditTrail.sql"

if [ "$ADD_CREATE_SCHEMA_STATEMENTS" = true ]; then
	# @todo add default collation to be utf8mb4
	# @todo allow target db name to different from source db name
	if [ "$ACTION" = all ] || [ "$ACTION" = demo ]; then
		echo "CREATE DATABASE IF NOT EXISTS $MYSQL_DATABASE;" > "$TARGET_DIR/demo.sql"
		echo "USE $MYSQL_DATABASE;" >> "$TARGET_DIR/demo.sql"
		echo "" >> "$TARGET_DIR/demo.sql"
	fi
	if [ "$ACTION" = all ] || [ "$ACTION" = default ]; then
		echo "CREATE DATABASE IF NOT EXISTS $MYSQL_DATABASE;" > "$TARGET_DIR/default.sql"
		echo "USE $MYSQL_DATABASE;" >> "$TARGET_DIR/default.sql"
		echo "" >> "$TARGET_DIR/default.sql"
	fi
else
	if [ "$ACTION" = all ] || [ "$ACTION" = demo ]; then
		echo "" > "$TARGET_DIR/demo.sql"
	fi
	if [ "$ACTION" = all ] || [ "$ACTION" = default ]; then
		echo "" > "$TARGET_DIR/default.sql"
	fi
fi

if [ "$ADD_CREATE_TABLES_STATEMENTS" = true ]; then
	if [ "$ACTION" = all ] || [ "$ACTION" = default ]; then
		TARGET_FILE=default.sql
	else
		TARGET_FILE=demo.sql
	fi

	# @todo review the list of excluded tables
	mysqldump -h"$MYSQL_HOST" -P"$MYSQL_PORT" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --skip-opt --create-options --skip-set-charset --no-data  \
		--ignore-table="${MYSQL_DATABASE}.buckets" \
		--ignore-table="${MYSQL_DATABASE}.levels" \
		--ignore-table="${MYSQL_DATABASE}.mrpparameters" \
		--ignore-table="${MYSQL_DATABASE}.mrpplanedorders" \
		--ignore-table="${MYSQL_DATABASE}.mrprequirements" \
		--ignore-table="${MYSQL_DATABASE}.mrpsupplies" \
		"$MYSQL_DATABASE" | sed 's/ AUTO_INCREMENT=[0-9]*//g' >> "$TARGET_DIR/$TARGET_FILE"

	# disable foreign keys checking - not needed, as it is part of the mysqldump output
	#echo "" > "$TARGET_DIR/default.sql"
	#echo "SET FOREIGN_KEY_CHECKS = 0;" > "$TARGET_DIR/default.sql"

	if [ "$ACTION" = all ]; then
		cat "$TARGET_DIR/default.sql" >> "$TARGET_DIR/demo.sql"
	fi
fi

if [ "$ACTION" = all ] || [ "$ACTION" = default ]; then
	# @todo review the list of included tables
	mysqldump -h"$MYSQL_HOST" -P"$MYSQL_PORT" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --skip-opt --skip-set-charset --quick --no-create-info "$MYSQL_DATABASE" \
		accountgroups \
		accountsection \
		bankaccounts \
		chartmaster \
		cogsglpostings \
		companies \
		config \
		currencies \
		edi_orders_seg_groups \
		edi_orders_segs \
		holdreasons \
		locations \
		paymentmethods \
		paymentterms \
		reportlinks \
		salesglpostings \
		scripts \
		securitygroups \
		securityroles \
		securitytokens \
		systypes \
		taxauthorities \
		taxauthrates \
		taxcategories \
		taxgroups \
		taxprovinces \
		unitsofmeasure \
		www_users \
		>> "$TARGET_DIR/default.sql"

	echo "" >> "$TARGET_DIR/default.sql"
	# @todo figure out why we had this update here...
	#echo "UPDATE systypes SET typeno=0;" >> "$TARGET_DIR/default.sql"
	echo "INSERT INTO shippers VALUES (1,'Default Shipper',0);" >> "$TARGET_DIR/default.sql"
	echo "UPDATE config SET confvalue='1' WHERE confname='Default_Shipper';" >> "$TARGET_DIR/default.sql"
	#echo "SET FOREIGN_KEY_CHECKS = 1;" >> "$TARGET_DIR/default.sql"
fi

if [ "$ACTION" = all ] || [ "$ACTION" = demo ]; then
	# @todo review the list of excluded tables
	mysqldump -h"$MYSQL_HOST" -P"$MYSQL_PORT" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --skip-opt --skip-set-charset --quick --no-create-info \
		--ignore-table="${MYSQL_DATABASE}.mrpsupplies" \
		--ignore-table="${MYSQL_DATABASE}.mrpplanedorders" \
		--ignore-table="${MYSQL_DATABASE}.mrpparameters" \
		--ignore-table="${MYSQL_DATABASE}.levels" \
		--ignore-table="${MYSQL_DATABASE}.mrprequirements" \
		"$MYSQL_DATABASE" >> "$TARGET_DIR/demo.sql"

	#echo "" >> "$TARGET_DIR/demo.sql"
	#echo "SET FOREIGN_KEY_CHECKS = 1;" >> "$TARGET_DIR/demo.sql"
fi
