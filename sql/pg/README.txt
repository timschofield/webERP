
To use Postgres in preference to MySQL apply the script weberp28.psql
against the Postgres SQL server and ensure that postgres is selected in
config.php as the SQL server and the appropriate postgres server host
is specified with user name and password.

Conversion of a MySql database to Postgres

# This program was written by Danie Brink brink@nas.co.za
# please note the software is distributed under the :
# GPL Licence, please see the GPL Licence in README.txt
# I take no responsibility consiquetial or
# inconsequential for any damges that may result from
# the use of this software.

Please note that Python is required
-------------------------------------------------
- First Dump the mysql database using command as below
- please substitute database and filenames as
- required
------------------------------------------------

mysqldump -u root -c -Q weberp28 > ./weberp28.sql



------------------------------------------------
- Second use my2pg to create postgres dataset of
-   the mysql data set
------------------------------------------------

my2pg weberp28.sql weberp28.psql


-----------------------------------------------
- Thirdly create postgres database as required
-   and then import data
----------------------------------------------

createdb weberp28
cat ./weberp28.psql | psql weberp28

-----------------------------------------------
- Fourthly copy the script in this directory
- ConnectDB.pg.inc over the existing mysql
- includes/ConnectDB.inc
- The variables in config.php
- $host = "localhost";
- $DatabaseName = "weberp";
- $dbuser = "weberp_db_user";
- $dbpassword = "weberp_db_pwd";
- must now refer to the Postgres server
----------------------------------------------
