# Main Changes

This file contains a high-level overview of the main changes, starting with v5.

For detailed changelogs of v5 and later, please look at the git commit logs for the `master` branch, available at
https://github.com/timschofield/webERP/commits/master/.

For detailed changelogs of v4 and earlier, please refer to [CHANGELOG.md].

## [v5.0.0] - (unreleased)

* increased minimum php requirements to version 8.1
* removed support for php extension `mysql` to connect to the database. Use `mysqli` instead
* db tables are now created using the `utf8_mb4` character set if the database supports it, instead of `utf8_mb3`,
  to allow full support of emojis and other unicode niceties (NB: this is actually not yet merged ;-)
* rewritten the installer
* fixed the XMLRPC API
* updated the pdf report writer library TCPDF to version 6.XX
* introduced a new pdf report writer library: DomPDF, to eventually replace all usage of TCPDF
* new and improved database upgrade system
* new login screen
* new agents commission system
* new dashboard system
* new system for processing regular payments
* new General Ledger budget system
* new budget system
* new popup context-sensitive help system
* set the default session timeout for new users to 10 minutes instead of 5
* automatically log out the user, and then return them to the correct module when they log back in
* improved compatibility with MySQL/MariaDB strict mode
* fixed sending emails to multiple addresses
* changed the default timezone to be Auckland
* various directory restructuring. Image files are now in `images`, external dependencies in `vendor`. Unused sql dumps
  have been removed
* the `report_runner.php` script has been moved to the `/bin` directory. It also does not use anymore the option
  to set the installation directory (it gets it automatically)
* changed the following variable in config.php:
  `$MySQLPort` -> `$DBPort`
* introduced usage of Composer to manage dependencies
* improved support for installing webERP in a directory below the webserver root
* update dependencies to their latest version (barcodepack, polyfill-gettext, phplot, phpmailer, phpspreadsheet, phpxmlrpc)
* moved documentation files to Markdown format, to ease viewing directly on GitHub
* introduced file `robots.txt` to avoid accidental indexation of site contents
* improved the debugging of failed SQL queries when `$Debug` is set to 1
* css styling changes
* various bugfixes
