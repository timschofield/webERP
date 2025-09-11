# Testing webERP

The webERP test suite consists mostly of functional tests - tests accessing the web interface rather than
driving directly the single code components. It is built using PHPUnit and the DomCrawler component.

The testsuite is run automatically on GitHub to validate every commit and pull request. It is also possible to run
tests locally to check that there are no bugs introduced before submitting a pull request.

In order to do so, it is necessary to have already set up:

* a webserver running php and configured to serve the local webERP installation
* a database (currently only mariadb and mysql are supported), which must be reachable from the webserver - note that
  if you don't have one set up, you can run one via Docker using the included scripts (see bottom of this page)
* the php tool `composer`

_NB_ the test suite is not included if you have downloaded webERP from GutHub as a tarball. You need to have
gotten it via `git clone`.

There are two main modes of operation:
1. run the tests using a dedicated database schema, loaded with demo data
2. run the tests using a pre-existing webERP database schema, loaded with data of your choice

_IMPORTANT:_ When in mode 2, the data in the database will be permanently modified by the test suite. DO NOT RUN TESTS
AGAINST YOUR PRODUCTION DATABASE!


## Testing workflow

1. prerequisites: set up the webserver, php, database, and install webERP (see the installation instructions).

   Make sure you have the following PHP extensions installed and enabled:
   `bcmath, calendar, curl, ftp, gd, gettext, iconv, mbstring, mysqli, simplexml, xdebug, xml, zip, zlib`

   Make sure you have the following configuration settings in your `php.ini`: ...TODO...

   Make sure that the `composer` command is in your PATH. If not, run `sudo ./tests/setup/setup_composer.sh` to
   have it downloaded and installed in the `/usr/local/bin` folder

2. install the php test dependencies by running, in the webERP root directory, the cli command

   `./tests/setup/setup_dependencies.sh`

   Note: if the `composer` command is not in your path, or is named differently, set the env var COMPOSER to point
   to the correct command before running the script

   _NB:_ after running this command, the local `./vendor/` directory will be modified. Please do not commit back any of
   those changes to the `master` branch on GitHub! See step 6 below on how to undo those changes.

3. set up the test configuration for your environment: in the webERP root directory, create a file `phpunit.xml`
   with the following contents, tweaked with the correct values

	```
	<?xml version="1.0" encoding="UTF-8" ?>
	<phpunit>
		<php>
			<env name="TEST_TARGET_PROTOCOL" value="http" />
			<env name="TEST_TARGET_HOSTNAME" value="localhost" />
			<env name="TEST_TARGET_PORT" value="" />
			<env name="TEST_TARGET_BASE_URL" value="/...some path.../webERP/" />
			<env name="TEST_DB_TYPE" value="mysqli" />
			<env name="TEST_DB_HOSTNAME" value="localhost" />
			<env name="TEST_DB_PORT" value="3306" />
			<env name="TEST_DB_USER" value="root" />
			<env name="TEST_DB_PASSWORD" value="root" />
			<env name="TEST_DB_SCHEMA" value="weberp_test" />
			<env name="TEST_USER_ACCOUNT" value="admin" />
			<env name="TEST_USER_PASSWORD" value="weberp" />
			<env name="TEST_USER_EMAIL" value="admin@weberp.org" />
		</php>
	</phpunit>
	```

   _NB:_ the TEST_DB_SCHEMA might be either an existing, throw-away webERP database, prefilled with data, or
   the name of a new db schema which will be created on the fly in the next step

   _NB:_ if the tests fail with unexpected error messages, check that in the `phpunit.xml` file you have set values
   (empty strings are ok) for all the env variables defined in file `phpunit.dist.xml` - it might be that the example
   given above here has not been updated to keep track of recent developments...

4. (optional) create the test database schema and fill it with demo data: run

   `./vendor/bin/phpunit tests/install`

5. run the test suite

   `./vendor/bin/phpunit tests/run`

6. after your testing is complete, to avoid accidentally committing to git the test suite tools found

   ```
   composer install --ignore-platform-reqs --no-dev
   composer --ignore-platform-reqs dump-autoload --optimize --no-dev
   ```

   Also, if you created a new db schema in step 4 above, feel free to drop it

   `TODO: command to be developed...`


## Writing tests

TO BE DOCUMENTED...

for the moment, look at an example in `tests/install/InstallerTest.php`


## Using the test scripts to run a specific version of a database

In short: you can _easily_ run any version of MySql and MariaDB locally, and use it for webERP.

The prerequisite is to have Docker installer.

The script to run them is `tests/setup/setup_db.sh` run it with `-h` for help.

NB: the db container does not stop once started. To stop it, run

	`docker ps`

then

	`docker stop $id`

where `$id` is the id of the container, gotten from the 1st command
