<?php

# PHPlot test: Data label extended custom formatting - 2c, candlesticks
# This is a parameterized test. See the script named at the bottom for details.
$plot_type = 'candlesticks';
$data_type = 'text-data';
$ny = 1;
$dvls = False; // This plot & data type does not do Data Value Labels.
require 'dlexformat.php';
