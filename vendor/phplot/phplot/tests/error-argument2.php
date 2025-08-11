<?php

# PHPlot error test - argument error with handler

if (version_compare(PHP_VERSION, 8.4, '>=')) {
    echo "Skipping test because it generates a warning with php 8.4 and later\n";
    exit(2);
}

require 'esupport.php';
set_error_handler('test_catch_exit');
require 'error-argument.php';
