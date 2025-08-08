#!/usr/bin/env bash

# Creates 2 files:
# ./sql/mysql/default.sql - a dump of the current db, with only minimal data
# ./sql/mysql/demo.sql - a dump of the current db, with all data

set -e

help() {
    printf "Usage: dump_database.sh

Used historically to create a) an 'empty' database to use when creating a new company,
and b) a 'demo' database containing operational data for learning and testing.

Uses env vars: MYSQL_HOST, MYSQL_PORT, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE

NB: the db user must have sufficient permissions to be able to execute mysqldump. You might need to use the admin
user instead of the user used to run the application.

NB: truncates table audittrail on the live db in use.
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

# @todo add support for mysql host and port env vars
MYSQL_USER="${MYSQL_USER:-root}";
MYSQL_PASSWORD="${MYSQL_PASSWORD:-root}";
MYSQL_DATABASE="${MYSQL_DATABASE:-weberpdemo}"
MYSQL_HOST="${MYSQL_HOST:-localhost}"
MYSQL_PORT="${MYSQL_PORT:-3306}"

BASE_DIR="$(dirname -- "$(dirname -- "$(realpath "${BASH_SOURCE[0]}")")")";
# @todo allow this to be specified via a cli option
TARGET_DIR="$BASE_DIR/sql/mysql"

mysql -u"$MYSQL_USER"  -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" < "$BASE_DIR/build/TruncateAuditTrail.sql"

#echo "-- Created by dump_database.sh" > "$TARGET_DIR/default.sql"
echo "SET FOREIGN_KEY_CHECKS = 0;" > "$TARGET_DIR/default.sql"
echo "" >> "$TARGET_DIR/default.sql"

# @todo why do we force the database creation in demo.sql but not in default.sql?
echo "CREATE DATABASE IF NOT EXISTS $MYSQL_DATABASE;" > "$TARGET_DIR/demo.sql"
# @todo add default collation to be utf8mb4
echo "USE $MYSQL_DATABASE;" >> "$TARGET_DIR/demo.sql"
echo "" >> "$TARGET_DIR/demo.sql"

# @todo review the list of excluded tables
mysqldump -h"$MYSQL_HOST" -P"$MYSQL_PORT" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --skip-opt --create-options --skip-set-charset --no-data  \
	--ignore-table=${MYSQL_DATABASE}.buckets \
	--ignore-table=${MYSQL_DATABASE}.levels \
	--ignore-table=${MYSQL_DATABASE}.mrpparameters \
	--ignore-table=${MYSQL_DATABASE}.mrpplanedorders \
	--ignore-table=${MYSQL_DATABASE}.mrprequirements \
    --ignore-table=${MYSQL_DATABASE}.mrpsupplies \
	"$MYSQL_DATABASE" | sed 's/ AUTO_INCREMENT=[0-9]*//g' >> "$TARGET_DIR/default.sql"

cat "$TARGET_DIR/default.sql" >> "$TARGET_DIR/demo.sql"

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

# @todo review the list of excluded tables
mysqldump -h"$MYSQL_HOST" -P"$MYSQL_PORT" -u$MYSQL_USER -p$MYSQL_PASSWORD --skip-opt --skip-set-charset --quick --no-create-info \
	--ignore-table=${MYSQL_DATABASE}.mrpsupplies \
	--ignore-table=${MYSQL_DATABASE}.mrpplanedorders \
	--ignore-table=${MYSQL_DATABASE}.mrpparameters \
	--ignore-table=${MYSQL_DATABASE}.levels \
	--ignore-table=${MYSQL_DATABASE}.mrprequirements \
	"$MYSQL_DATABASE" > "$TARGET_DIR/demo.sql"

echo "" >> "$TARGET_DIR/default.sql"
echo "UPDATE systypes SET typeno=0;" >> "$TARGET_DIR/default.sql"
echo "INSERT INTO shippers VALUES (1,'Default Shipper',0);" >> "$TARGET_DIR/default.sql"
echo "UPDATE config SET confvalue='1' WHERE confname='Default_Shipper';" >> "$TARGET_DIR/default.sql"
echo "SET FOREIGN_KEY_CHECKS = 1;" >> "$TARGET_DIR/default.sql"

echo "" >> "$TARGET_DIR/demo.sql"
echo "SET FOREIGN_KEY_CHECKS = 1;" >> "$TARGET_DIR/demo.sql"
