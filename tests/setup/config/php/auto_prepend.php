<?php

// This file is to be automatically loaded _before_ the execution of every php script, via the ini setting `auto_prepend_file`.
// We use it to set up all the ini settings and other stuff required for running the test suite

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_reporting', E_ALL);

ini_set('error_prepend_string', '<!-- TEST_ERROR_STRING -->');
ini_set('error_append_string', '<!-- /TEST_ERROR_STRING -->');

ini_set('include_path', '.');

//ini_set('log_errors', ...);
//ini_set('error_log', ...);

//ini_set('max_execution_time', ...);
//ini_set('memory_limit', ...);

/// @todo when in webserver execution context, handle $_COOKIE['PHPUNIT_SELENIUM_TEST_ID'] in order to collect remote
///       code coverage information (see how phpxmlrpc does it)
