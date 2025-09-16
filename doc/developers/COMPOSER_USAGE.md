# Using Composer with webERP

This document describes the webERP approach to managing dependencies on other php libraries.

The main requirement is to allow the software to be installed via a simple "download and unzip" workflow - or a
workflow based on "git clone", but still requiring no execution of manual commands to set it up.

Given the ubiquity of Composer to manage dependencies in the PHP world, the following decision was taken:
1. use Composer to manage the dependencies
2. "vendor" those dependencies, i.e. store them within the webERP codebase

This results in the need of carefully using Composer commands when adding new dependencies, updating the existing ones
to a new version, or setting up "dev" dependencies to run the test suite.


## Adding a new dependency

1. edit `composer.json`, add your dependency to the `require` element, using the desired version specifier, eg.
   for package `my/package` version 2.0 up to 2.XXX, use:

       	"require": {
   			"php": "^8.1",
   			"ext-ctype": "*",
   			"ext-mbstring": "*",
   			"ext-mysqli": "*",
   			"ext-xml": "*",
   			"dompdf/dompdf": "^3.1",
   			"my/package": "^2.0",

2. download the package into the `vendor` folder and update the autoloader:

       composer update --no-dev --optimize-autoloader --no-interaction --prefer-stable --prefer-dist my/package

   NB: please take care to use all of the above options

3. review all the modified files, add the modified files to a new Git branch, commit and send a Pull Request


## Updating an existing dependency

This workflow is similar to the one above:

1. if needed, edit `composer.json` and update the version specifier for the desired package, eg:

       	"require": {
   			"php": "^8.1",
   			"ext-ctype": "*",
   			"ext-mbstring": "*",
   			"ext-mysqli": "*",
   			"ext-xml": "*",
   			"dompdf/dompdf": "^3.1",
   			"my/package": "^3.0",

2. update the package into the `vendor` folder and update the autoloader:

       composer update --no-dev --optimize-autoloader --no-interaction --prefer-stable --prefer-dist my/package

   NB: please take care to use all of the above options

3. review all the modified files, add the modified files to a new Git branch, commit and send a Pull Request


## Merging the "Bump package" Pull Requests from Dependabot

Google's Dependabot will, from time to time, send PRs to update dependencies, usually when there are known security
issues with the version currently used by webERP.

Those PRs only update the `composer.json` and `composer.lock file`. This is not sufficient for the webERP setup.

The correct workflow to handle them is:

1. merge the Dependabot PR

2. pull the changes locally

3. download the new package version to the `vendor` folder and update the autoloader:

       composer update --no-dev --optimize-autoloader --no-interaction --prefer-stable --prefer-dist updated/package

4. review all the modified files, add the modified files to a new Git branch, commit and send a Pull Request


## Setting up "dev" dependencies to run the test suite

See [RUNNING_TESTS.md](RUNNING_TESTS.md)
