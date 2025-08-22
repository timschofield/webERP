# Installing webERP

This document describes how to perform a new webERP installation. See [UPGRADING.md](UPGRADING.md) if you
are upgrading an existing installation.

## Pre-requisites

### Server

* Web server (e.g. Apache HTTP Server or Nginx).

* PHP interpreter. PHP v8.1 or later is required, with
    MySQL or MariaDb extension (generally both use "mysqli"), gd, gettext (for translations),
    libxml, mbstring and ftp (optional for file transfer). The web server user must have full
    write privileges to the ./companies/ directory, and at least temporarily to the root
    directory for the web installer to save the created configuration file ./config.php.

* MySQL or v4.1+ or MariaDb 10.4+ (innodb tables MUST be enabled, which should be the default, but
    you can check my.cnf file to confirm, e.g. /etc/my.cnf or /usr/local/etc/mysql/my.cnf).

Detailed instructions for installing these components can be readily found in a web search. XAMPP is
recommended for development on a Windows(R) platform, see https://www.apachefriends.org/index.html.

webERP supported PostgreSQL at one time but does not currently due to lack of an interested
community member for maintenance and testing. If you are interested in the job, please create
a Discussion topic in the webERP repo!

Required PHP configuration (all are default values at least for XAMPP with PHP v8.2):

1. register_globals must be set to off (current default)
2. magic_quotes_gpc should be set to Off to avoid annoying "\" characters in some fields
3. session.use_cookies must be enabled

Configuring PHP is done by editing the server php.ini file. If you don't have file access on the
server, webERP provides a .htaccess file which can be used for those web servers that support it.

### Client

Any recent reputable web browser should work with webERP (e.g. Edge, Chrome, Safari, Firefox).

It may be useful to have a PDF reader available on the system the browser will be run on. Popular
choices on Windows are Acrobat Reader, Foxit PDF Reader and most web browsers.

It is recommended that PDF preferences be changed so that PDF documents are opened automatically
to avoid having to find the file and open it separately, and in a separate window or tab (otherwise
if viewing in a browser the PDF viewer window may replace webERP).

If you are using webERP with languages that require extended fonts to display you must use Acrobat
Reader v9.1 or above (webERP uses Adobe CID fonts which are bundled with Acroabat Reader but not
necessarily other PDF readers.


## Automatic Web Installer

Extract the webERP archive into your web-root directory. Ensure that the web-server can write to
the root directory (the installer creates the webERP configuration file config.php file). You may
need to alter directory and/or file permissions using cpanel or other means, and the permissions
can be changed back after installation is complete.

Browse to the site (URL):

http://yourhost/webERP/

Where "yourhost" is the host web-server. The installer will start automatically and will will first check
for required file permissions and server configuration. Installation will not proceed unless the checks
pass.

Follow the instructions in the web installer to complete installation, then log out and back in
to configure the system for use.


## Manual Installation

Installation can be performed manually if needed. However, the automated update system MUST be
used to update the database to the current schema. The webERP project maintains the database dynamically
with a series of consecutive schema updates, a single SQL file with the current schema does not exist.

The installation consists of:

1. Copy PHP scripts and include files to a directory under the web server root directory
2. Configure file level server security (optional)
3. Access the webERP web installer to create a database (if the specified database does not already exist)
    and optionally populate with demo data. The installer will also create the webERP configuration
    file (config.php) which contains the database connection details including username and password, and
    the initial "admin" user details.
4. Log into the system as the initial admin user to update the database
5. Configure system parameters, enter company information and create additional users as needed.

### 1.  Copy PHP Scripts

Download and extract a webERP archive to the web server root directory (possibly in a sub-directory to
keep webERP separate from other applications). This will vary depending on the server operating system
and installation.

In Windows, the web root may be:

	C:\Program Files\Apache Group\Apache\htdocs

	C:\xampp\htcos\

or if using apache2triad,

	C:\apache2triad\htdocs\

In Unix/Linux, the web root may be:

	/usr/local/httpd/htdocs/

	/usr/local/apache2/htdocs/

	/srv/www/htdocs

or

	/usr/local/www


Extracting the webERP archive will create the webERP directory under the web server root directory with
all scripts and other files.


### 2. Configure file-level security (optional)

You can add an extra layer of security by restricting access to webERP system using a server level username
and password, in addition to the username and password authentication built into webERP
itself. However, configuring the server for TLS encryption and using strong username passwords will likely
be adequate.

#### 2.1

Assuming you are using a web server that supports .htaccess (e.g. Apache HTTP Server), edit the webERP
`.htaccess` file as follows:

	php_flag magic_quotes_gpc off
	php_flag register_globals off

	DirectoryIndex index.php index.htm index.html index.html.en

	IndexIgnore .htaccess */.??* *~ *# */HEADER* */README* */_vti

	AuthUserFile /var/www/mycompany/webERP/.htpasswd
	AuthGroupFile /dev/null
	AuthName webERPPassword
	AuthType Basic

	<Limit GET POST>
	require valid-user
	</Limit>

	# Disallow access to this file
	<Files .htaccess>
	order allow,deny
	deny from all
	</Files>


#### 2.2

Create a password file called .../htdocs/webERP/.htpasswd:

	# htpasswd -n john.smith

#### 2.3

Use command "htpasswd -n" to create sample lines which will be of the format

	john.smith:0123456789012345

Where '0123456789012345' is the encrypted password for john.smith. Create one line for each webERP user.
Note that you will need to enter the (unencrypted) password whenever you access
the webpages in .../webERP.


### 3. Access the webERP web installer

The webERP web installer to create a database (if the specified database does not already exist)
and optionally populate with demo data. The installer will also create the webERP configuration
file (config.php) which contains the database connection details including username and password, and
the initial "admin" user details.

#### 3.1 Create database manually

If desired, the database can be created manually before accessing the webERP web installer. The installer
will use an existing database if one exists.

webERP requires either MySQL version 4.1 or greater or MariaDb 10.4+

In webERP each company has its own database. For example, if you have a company called MyCompany, webERP
expects there will be database named mycompany (in lower case). Before starting to install webERP, ensure
that there is no database on your system with the same name. If there is, you will need to either remove it,
rename it, or choose a different name for your company.

For MySQL, Innodb tables MUST be enabled. Innodb tables allow database transactions and foreign key support,
these are critical requirements for webERP and ERP software in general. Innodb tables require some parameters
to be configured in my.cnf - see the examples in the MySQL manual under table types - Innodb tables.

Both MySQL and MariaDb provide a "mysql" command line client. To run "mysql" on Windows, open a terminal window
(or bash window, if installed) and enter the appropriate path for the "mysql" command with the appropriate
username and password authentication.

If using XAMPP, open a shell from the XAMPP Control Panel and enter the command "mysql -u root" to use the
default MariaDb user "root" with no password. If you have installed MySQL or MariaDb manually, you will need
to give the full path to mysql.exe, e.g. C:\mysql\bin\mysql.exe if it is not in the default path (and also add
the full path to the commands below as mysql with e.g. prefix with "C:\mysql\bin\").

You need to know the database server username and password.

The MySQL and MariaDB installation default is:

user root
password ""

but think twice before using this account. For security, the password should be changed to at least have a
a password, but better would be to configure MySQL or MariaDb for secure operation and create a dedicated user
for webERP with access only to webERP databases. This prevents a compromise of one database from affecting
other databases.

Paraphrased from the MySQL manual:

	The MySQL root user is created as a superuser who can do anything. Connections must be made from the local host.
	NOTE: The initial root password is empty, so anyone can connect as root without a password and be granted
    all privileges.

	Because your installation is initially wide open, one of the first things you should do is specify a password
    for the MySQL root user. You can do this as follows (note that you specify the password using the PASSWORD()
    function):

	You can, in MySQL Version 3.22 and above, use the SET PASSWORD statement:

	shell> mysql -u root mysql
	mysql> SET PASSWORD=PASSWORD('new_password');

    where 'new_password' is the new password you chose for the root user.

Also paraphrased from the MySQL manual:

	...if you have changed the root user password, you must specify it for the mysql commands below.

	You can add new users by issuing GRANT statements:

	shell> mysql --user=root  -p 'new_password' mysql

	mysql> GRANT ALL PRIVILEGES ON *.* TO weberp_db_user@localhost
        	   IDENTIFIED BY 'some_pass' WITH GRANT OPTION;

    Where 'some_pass' is a password of your choice for the new user 'weberp_db_user'. Note that this user
    weberp_db_user can only connect from the local machine so if the web server is on a different machine to
    the MySQL server then you need to give privileges to connect from other computers. See the MySQL manual. Note
    also that the "'" quote symbol must be typed.


#### 3.2 Create database schema manually (optional demo data)

An sql script id provided which can be used to manually create the database schema and load demo
data.

	./install/sql/demo.sql

It contains a minimal amount of demonstration data and bogus company setup so that transactions can be tried to
see how the system works.

To use the default.sql file:

1. create a database e.g. using phpMyAdmin

You can also the following SQL commands to the top of the demo.sql script

	CREATE DATABASE mycompanyname DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_unicode_ci;
	USE mycompanyname

Instead of mycompanyname use the name of your company. Note that the name of the database must all be in
lower case.

This creates a weberp database and populates it with only the very basic data to start off.

	shell > mysql --user=weberp_db_user -p mysql < /path_to_the_sql_script/demo.sql

or

	shell > mysql --user=weberp_db_user --p mysql < /path_to_the_sql_script/demo.sql

as required. You will be prompted for the mysql password that you created earlier.

Confirm the mycompanyname database now exists.

Use the demo.sql file if you wish demo data to be loaded.


#### 3.3 Create the companies/ directory

Copy the ./companies/weberpdemo to ./companies/mycompanyname


#### 3.4 Edit config.php

`config.php` is the low-level configuration file for webERP and is site specific. The `config.distrib.php`
file is provided as a template.

Copy config.distrib.php to config.php. Edit config.php with database connection parameters and any
other relevant data.


### 4. Log into the system as the initial admin user to update the database

Browse to the site URL e.g. http://yourdomain/weberp (if you are using XAMPP http://localhost/weberp).

Enter the web access password if file-level security was enabled (see above), accept all cookies and
enter the initial admin username and password (admin/weberp if you accepted the default).

- Select mycompanyname from the drop down list.
- Enter the user name 'admin'
- Enter password 'weberp'

(do not enter the quotation marks).

You will be prompted if there are required updates to perform. If so, perform the updates then logout and back in.


## 5. Configure system parameters and enter company information

It is imperative to go through each of the WebERP Setup menus [Main Menu > Setup > ...] and enter
appropriate information for your company - particularly the company information and the configuration
settings. Each of these settings together with the narrative explaining what each of the settings does
should be reviewed in detail before starting using the system.

The online manual includes a section called "Getting Started" which contains extensive information and
worth reading before going live.


## Configuration file (config.php)

webERP gets low-level configuration parameters, such as the database connection, from the config.php
file. As this file is typically site-specific, the template file config.distrib.php is provided
to use as a template.

config.distrib.php is well commented (lines starting with // are comments and not processed by
PHP). Critical parameters are the computer $host, the $DBType, the $DBUser and the $DBPassword. The
remaining variables can typically be left at their defaults.

Sha1 encryption requires PHP v4.3 or greater. If you are attempting to use a prior version (not
recommended) you could try md5 encryption.

webERP determines the appropriate database name from the sub-directory names in ./Companies/.
When a new database is created, the /Companies/weberpdemo/ sub-directory must be manually copied
to a new sub-directory with the same name as the database that was created.

(in prior versions the variable $DatabaseName was required in config.php, this is no longer the case).


## Troubleshooting

You can get unexpected behavior in the installer if you happen to have used the installer multiple
times on the same codebase with different database names (specifying a database name results in
creating a copy of the companies/weberpdemo directory with the same name as the specified database,
and a "Companies.php" file in the directory containing the full company name which is used in the
login screen).

You may also get unexpected behavior attempting to login to a new or upgraded webERP system (e.g. "hey,
I thought I deleted that company!" or "Why doesn't my password seem to work?"). Also, the installer
(and webERP itself) can store information in the users' browser which can affect the behavior of a new
or upgraded site, in particular when logging in.

The web installer has worked for everyone so far, but it's only guaranteed when starting with a
fresh or clean environment.

1. Delete any databases (e.g. if using XAMPP, click the MySQL [Admin] button to use the PhpMyAdmin
     web app)
2. Delete all companies/ subdirectories EXCEPT for weberpdemo (you could extract an archive or clone the
     webERP project repo again if you really want to be sure you had all the correct files).
3. Delete all browser data. Ctrl-F5 isn't enough, you must delete ALL browser data ("for all time" works
     although a shorter period may be sufficient).
4. Delete config.php (it will be re-created by the web installer)
5. Browse to the site to login (e.g. if using XAMPP, http://localhost/weberp)


## Security

Note: Once you have installed webERP it is important to remove the installer files by deleting the
installation directory and all the scripts underneath it. It is also wise to change the permissions on the
config.php file to ensure that it can no longer be written to by the web-server.


## Legal

This program is free software; you can redistribute it and/or modify it under the terms of the
GNU General Public License as published by the Free Software Foundation; version 2 (the "GPL v2").

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Refer to the
GPL v2 for more details.

The GPL v2 is provided in doc/LICENSE.txt.

(C) The webERP project
