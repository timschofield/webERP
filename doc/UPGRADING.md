# Upgrading webERP

This document describes upgrading an existing webERP installation. Refer to [INSTALL.md](INSTALL.md)
if you are performing a new installation.

A new version of webERP can introduce new database tables or columns or modify existing
columns, new PHP scripts may not work correctly until the database schema is upgraded to
correspond to the new scripts.

Always make a backup of the database BEFORE starting an upgrade!

In webERP v4.1 a new automated database upgrade procedure was introduced. To perform a system
upgrade, extract the new archive into the existing installation directory (or `git pull` if
the source was git cloned from the webERP project repository), browse to the webERP site and
login as a user with admin privileges. webERP will determine if any database updates are necessary
and prompt for approval before applying them.


## General upgrade procedure

1. Warn users webERP will be going offline for maintenance. If possible, schedule downtime
     and warn users in advance (e.g. yesterday and again this morning). Also warn users that any
     work in process that has not been saved when the system goes down will be lost and will need
     to be re-entered when webERP is back online.

2. Stop new users from logging in by setting `[Main Menu > Settings > System Parameters > Perform
     Database Maintenance at Login]` to "Allow SysAdmin Access Only"

3. (Optional) Stop webERP site. For the highest degree of database integrity, you may consider
     stopping the system during the upgrade. The results are unpredictable when an upgrade involves
     upgrading the database schema and an already-logged user performs a transaction using new
     PHP code, which exercise expected new database features, but before the database schema
     has been upgraded.

     Also, the files of the new version are not updated simultaneously. There is a finite risk a
     transaction involving multiple PHP files could be executed using some new files and some
     old files, also with unpredictable results. However, depending on the number of users and the
     upgrade itself, this risk may be too low to consider (e.g. server hardware failure might be
     more likely to occur).

     Also note that caching (e.g. PHP code, web page, database queries, etc.) can also have an
     affect the actual or perceived stability of an upgrade.

4. Backup the webERP directory and all databases to restore should any issues be encountered
     during the upgrade procedure.

5. Upgrade webERP files by either:

    - extract the new archive into the webERP directory and overwrite existing files, or
    - if git was used to clone the webERP Git source repo (i.e. if the installation was done by cloning
      the GitHub project repo), you can use "git pull" to update.

6. Put the web site back online (e.g. restart web server)

7. Log into each company as an admin user to trigger the automatic database upgrade procedure. Each
     database (company) must be upgraded individually.

The new upgrade system cannot identify database schema versions prior to 3.11 and will prompt for
the database version (the upgrade system is compatible with version 3.00 onwards).

Should you install using a new directory, you are essentially performing a new installation and
you must manually copy any site-specific files from the old version yourself, such as:

1. config.php
2. webERP/companies/*
3. any modified scripts (any modified scripts should be saved in a separate directory to aid updating
   as required.


## Upgrading from a previous version

### Upgrading from a database version after 4.10

Variable naming in config.php was changed in version 4.10 to be consistent with the rest of the system.

$dbType changed to $DBType
$dbuser changed to $DBUser
$dbpassword changed to $DBPassword
$allow_demo_mode changed to $AllowDemoMode
$rootpath changed to $RootPath

The upgrade script should modify your existing config.php file automatically to change these variable
names.


### Upgrading from a database version after 4.09

A new CSS structure was released with 4.09 and upgraders made need to clear their browser cache for the
new CSS to render correctly (reported by a user with Internet Explorer 9).


### Upgrading from database version 3.11 to 4.x

PHP 5.3 is required as of version 4.x because of simpleXML module is now used for XML definition of
report formats and is only available with PHP 5+.


### Upgrading from database version prior to 3.05

Prior to v4.01, updating the database was performed using SQL scripts in webERP/sql/mysql/country_sql/.
The upgrade scripts assume database name "webERP", if your database name is different you must edit
the script to add the following line to the top before running the the script in a terminal.

    USE mydatabase;

    where "mydatabase" is the name of your database.

You do not need to to edit the file if you are using phpMyAdmin to run the upgrade script. When using
phpMyAdmin, the database name is selected in the web GUI and does not need to be specified in the SQL
script. However, there were many changes from version 2.9b and 3.0 and the SQL upgrade script should be
run from a terminal shell session to avoid PHP execution timeout.

Use the following shell command to run the script:

    $ mysql --user=weberp_db_user --password='some_pass'  < upgradeXX.X-YY.Y.sql

where:

  - "weberp_db_user" is the mysql user name
  - "some_pass" is the password for weberp_db_user
  - "upgradeXX.X-YY.Y.sql" is the SQL upgrade script.
	  - XX.XX is the old version number
	  - YY.YY is the new version number

Note you may need to give a full path to the mysql utility (e.g. /usr/local/mysql/bin/mysql) as well as
the SQL upgrade script (e.g. sql/mysql/upgrade4.15.1-4.15.2.sql).

and "upgradescriptname.sql" is the name of an SQL upgrade script.


### Upgrading from database version 3.04 to 3.05

3.05 now has Dave Premo's report writer scripts included in the distribution - additional tables are required for
this functionality. Also, 3.05 allows for weighted average stock valuation - using the existing data fields and
retaining integrated general ledger stock values. This requires cost information to be copied over into the grns
table.

The upgrade script Z_Upgrade_3.04-3.05.php applies all the additional tables to the database and does the data
conversions required without any manual intervention.


### Upgrading from database version 3.01 to 3.02

3.02 includes extensive changes to the tax schema in webERP and also allows for the same item on a sales order
multiple times. This requires all existing sales orders to be updated with an appropriate number to avoid strange
behaviour. A special script must be run to effect these changes after the SQL script has been run. The script
Z_Upgrade_3.01-3.02.php must be opened in the browser to effect these changes.


### Upgrading from database version 2.9B TO 3.0

There are extensive changes to the database and the upgrade2.9b-3.0.sql may take some time to run depending on
how much data there is in the system. A backup of the 2.9b database dump should be taken prior to attempting to
run the upgrade script.

IMPORTANT: Note that mysql version 4.1.8 or greater is required because from the mysql change log:
	"Fix two hangs: FOREIGN KEY constraints treated table and database names as case-insensitive. RENAME TABLE t TO T
    would hang in an endless loop if t had a foreign key constraint defined on it. Fix also a hang over the dictionary
    mutex that would occur if one tried in ALTER TABLE or RENAME TABLE to create a foreign key constraint name that
    collided with another existing name. (Bug #3478)"


### Upgrading from database version 2.8 to 2.9

Unfortunately I was overzealous with the foreign keys and made up one for StockID in ShipmentCharges;
this needs to be dropped but the name of this key is generated by Innodb, so I cannot create a script to ditch it!!
You will need to remove this yourself - otherwise you will not be able to create shipment charges.
Using the new db scripts will of course generate dbs without this foreign key.
