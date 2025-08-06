<?php

# PHPlot test: label (tick, axis data, data value) color variations - case 4
# This is a parameterized test. See the script named at the bottom for details.
$colors = array(
    'text' => True,
    'ticklabel' => True,
    'datalabel' => True,
    'datavaluelabel' => True,
);
$plot_type = 'stackedbars';
require 'labelcolor.php';
