<?php

# PHPlot test: Data label extended custom formatting - 2f, stackedarea
# This is a parameterized test. See the script named at the bottom for details.
$plot_type = 'stackedarea';
$data_type = 'text-data';
$ny = 6;
$max = 5;
$dvls = False; // This plot & data type does not do Data Value Labels.
require 'dlexformat.php';
