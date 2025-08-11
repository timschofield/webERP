#!/usr/bin/env bash

# Used historically to create a) an "empty" database to use when creating a new company,
# and b) a "demo" database containing operational data for learning and testing.
#
# Creates 2 files:
# ./sql/mysql/country_sql/default.sql - a dump of the current db, with only minimal data
# ./sql/mysql/country_sql/demo.sql - a dump of the current db, with all data
#
# NB: truncates table audittrail on the live db in use

set -e

# @todo add support for mysql host and port env vars
MYSQL_USER="${MYSQL_USER:-root}";
MYSQL_PWD="${MYSQL_PWD:-a}";
MYSQL_DATABASE="${MYSQL_DATABASE:-weberpdemo}"

BASE_DIR="$(dirname -- "$(dirname -- "$(realpath "${BASH_SOURCE[0]}")")")";
TARGET_DIR="$BASE_DIR/sql/mysql/country_sql"

mysql -u"$MYSQL_USER"  -p"$MYSQL_PWD" "$MYSQL_DATABASE" < "$BASE_DIR/build/TruncateAuditTrail.sql"

#echo "-- Created by dump_database.sh" > "$TARGET_DIR/default.sql"
echo "SET FOREIGN_KEY_CHECKS = 0;" > "$TARGET_DIR/default.sql"
echo "" >> "$TARGET_DIR/default.sql"

# @todo why do we force the database creation in demo.sql but not in default.sql?
echo "CREATE DATABASE IF NOT EXISTS $MYSQL_DATABASE;" > "$TARGET_DIR/demo.sql"
# @todo add default collation to be utf8mb4
echo "USE $MYSQL_DATABASE;" >> "$TARGET_DIR/demo.sql"
echo "" >> "$TARGET_DIR/demo.sql"

# @todo review the list of excluded tables
mysqldump -u"$MYSQL_USER" -p"$MYSQL_PWD" --skip-opt --create-options --skip-set-charset --no-data  \
	--ignore-table=buckets \
	--ignore-table=levels \
	--ignore-table=mrpparameters \
	--ignore-table=mrpplanedorders \
	--ignore-table=mrprequirements \
    --ignore-table=mrpsupplies \
	"$MYSQL_DATABASE" | sed 's/ AUTO_INCREMENT=[0-9]*//g' >> "$TARGET_DIR/default.sql"

cat "$TARGET_DIR/default.sql" >> "$TARGET_DIR/demo.sql"

# @todo review the list of included tables
mysqldump -u"$MYSQL_USER" -p"$MYSQL_PWD" --skip-opt --skip-set-charset --quick --no-create-info "$MYSQL_DATABASE" \
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
mysqldump -u$MYSQL_USER -p$MYSQL_PWD --skip-opt --skip-set-charset --quick --no-create-info \
	--ignore-table=mrpsupplies \
	--ignore-table=mrpplanedorders \
	--ignore-table=mrpparameters \
	--ignore-table=levels \
	--ignore-table=mrprequirements \
	"$MYSQL_DATABASE" > "$TARGET_DIR/demo.sql"

echo "" >> "$TARGET_DIR/default.sql"
echo "UPDATE systypes SET typeno=0;" >> "$TARGET_DIR/default.sql"
echo "INSERT INTO shippers VALUES (1,'Default Shipper',0);" >> "$TARGET_DIR/default.sql"
echo "UPDATE config SET confvalue='1' WHERE confname='Default_Shipper';" >> "$TARGET_DIR/default.sql"
echo "SET FOREIGN_KEY_CHECKS = 1;" >> "$TARGET_DIR/default.sql"

echo "" >> "$TARGET_DIR/demo.sql"
echo "SET FOREIGN_KEY_CHECKS = 1;" >> "$TARGET_DIR/demo.sql"
