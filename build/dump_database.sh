#!/usr/bin/env bash

set -e

# @todo grab these from env vars. Also, add support for mysql host and port, as well as a variable for the db schema name
MYSQL_USER=root;
MYSQL_PWD=a;

BASE_DIR="$(dirname -- "$(dirname -- "$(realpath "${BASH_SOURCE[0]}")")")";

mysql -u"$MYSQL_USER"  -p"$MYSQL_PWD" weberpdemo < "$BASE_DIR/build/TruncateAuditTrail.sql"

echo "SET FOREIGN_KEY_CHECKS = 0;" > "$BASE_DIR/sql/mysql/country_sql/default.sql"

mysqldump -u"$MYSQL_USER"  -p"$MYSQL_PWD"  --skip-opt --create-options --skip-set-charset --ignore-table=weberpdemo.mrpsupplies \
	--ignore-table=weberpdemo.mrpplanedorders --ignore-table=weberpdemo.mrpparameters --ignore-table=weberpdemo.levels \
	--ignore-table=weberpdemo.mrprequirements --ignore-table=weberpdemo.buckets --no-data weberpdemo | \
	sed 's/ AUTO_INCREMENT=[0-9]*//g' >> "$BASE_DIR/sql/mysql/country_sql/default.sql"

mysqldump -u"$MYSQL_USER"  -p"$MYSQL_PWD" --skip-opt --skip-set-charset --quick --no-create-info weberpdemo \
       accountgroups \
       bankaccounts \
       chartmaster \
       companies \
       cogsglpostings \
       currencies \
       holdreasons \
       locations \
       paymentterms \
       reportlinks \
       salesglpostings \
       systypes \
       taxauthorities \
       taxgroups \
       taxauthrates \
       taxcategories \
       taxprovinces \
       www_users \
       edi_orders_segs \
       edi_orders_seg_groups \
       config \
       unitsofmeasure \
       paymentmethods \
       scripts \
       securitygroups \
       securitytokens \
       securityroles \
       accountsection \
       > "$BASE_DIR/sql/mysql/country_sql/weberp-base.sql"

mysqldump -u$MYSQL_USER  -p$MYSQL_PWD --skip-opt --skip-set-charset --quick --ignore-table=weberpdemo.mrpsupplies \
	--ignore-table=weberpdemo.mrpplanedorders --ignore-table=weberpdemo.mrpparameters --ignore-table=weberpdemo.levels \
	--ignore-table=weberpdemo.mrprequirements --no-create-info weberpdemo \
	> "$BASE_DIR/sql/mysql/country_sql/weberp-demo_data.sql"

if [ -f "$BASE_DIR/sql/mysql/country_sql/demo.sql" ]; then rm  "$BASE_DIR/sql/mysql/country_sql/demo.sql"; fi
echo "CREATE DATABASE IF NOT EXISTS weberpdemo;" > "$BASE_DIR/sql/mysql/country_sql/demo.sql"
# @todo add default collation to be utf8mb4
echo "USE weberpdemo;" >> "$BASE_DIR/sql/mysql/country_sql/demo.sql"

cat "$BASE_DIR/sql/mysql/country_sql/default.sql" >> "$BASE_DIR/sql/mysql/country_sql/demo.sql"

cat "$BASE_DIR/sql/mysql/country_sql/weberp-base.sql" >> "$BASE_DIR/sql/mysql/country_sql/default.sql"
rm  "$BASE_DIR/sql/mysql/country_sql/weberp-base.sql"
cat "$BASE_DIR/sql/mysql/country_sql/weberp-demo_data.sql" >> "$BASE_DIR/sql/mysql/country_sql/demo.sql"
rm  "$BASE_DIR/sql/mysql/country_sql/weberp-demo_data.sql"

echo "SET FOREIGN_KEY_CHECKS = 1;" >> "$BASE_DIR/sql/mysql/country_sql/default.sql"
echo "UPDATE systypes SET typeno=0;" >> "$BASE_DIR/sql/mysql/country_sql/default.sql"
echo "INSERT INTO shippers VALUES (1,'Default Shipper',0);" >> "$BASE_DIR/sql/mysql/country_sql/default.sql"
echo "UPDATE config SET confvalue='1' WHERE confname='Default_Shipper';" >> "$BASE_DIR/sql/mysql/country_sql/default.sql"

echo "SET FOREIGN_KEY_CHECKS = 1;" >> "$BASE_DIR/sql/mysql/country_sql/demo.sql"
