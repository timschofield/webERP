# Testing webERP

The webERP test suite consists mostly of functional tests - tests accessing the web interface rather than
driving directly the single code components. It is built using PHPUnit and the DomCrawler component.

The testsuite is run automatically on GitHub to validate every commit and pull request. It is also possible to run
tests locally to check that there are no bugs introduced before submitting a pull request.

In order to do so, it is necessary to have already set up:

* a webserver running php and configured to serve the local webERP installation
* a database (currently only mariadb and mysql are supported), which must be reachable from the webserver
* the php tool `composer`

There are two main modes of operation:
1. run the tests using a dedicated database schema, loaded with demo data
2. run the tests using a pre-existing webERP database schema, loaded with data of your choice

_IMPORTANT:_ When in mode 2, the data in the database will be permanently modified by the test suite. DO NOT RUN TESTS
AGAINST YOUR PRODUCTION DATABASE!


## Testing workflow

1. prerequisites: set up the webserver, php, database, and install webERP (see the installation instructions).

   Make sure that the `composer` command is in your PATH. If not, run `sudo ./tests/setup/setup_composer.sh` to
   have it downloaded and installed in the `/usr/local/bin` folder

2. install the php test dependencies by running, in the webERP root directory, the cli command

   `./tests/setup/run_composer.sh`

   Note: if the `composer` command is not in your path, or is named differently, set the env var COMPOSER to point
   to the correct command before running the script

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
		</php>
	</phpunit>
   ```

   _NB:_ the TEST_DB_SCHEMA might be either an existing, throw-away webERP database, prefilled with data, or
   the name of a new db schema which will be created on the fly in the next step

4. (optional) create the test database schema and fill it with demo data: run

   `./vendor/bin/phpunit tests/install`

5. run the test suite

   `./vendor/bin/phpunit tests/run`

6. after your testing is complete, to avoid accidentally committing to git the test suite tools found

   `composer install --ignore-platform-reqs --no-interaction --no-dev`

   Also, if you created a new db schema in step 4 above, feel free to drop it

   `TODO: command to be developed...`


## Writing tests

TO BE DOCUMENTED...
