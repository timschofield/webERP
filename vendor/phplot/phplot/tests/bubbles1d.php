<?php

# PHPlot test: Bubble plot - Negative, 0 data
# This is a parameterized test. See script named below for details.
$subtitle = "Negative and Zero sizes";
$data = array(
    array('', 1, 1, 0),
    array('', 2, 2, -5),
    array('', 3, 3, -10),
    array('', 4, 4, -20),
    array('', 5, 5, -25),
);
require 'bubbles1.php';
