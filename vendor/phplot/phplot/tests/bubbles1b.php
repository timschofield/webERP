<?php

# PHPlot test: Bubble plot - Sequential values with matching min/max
# This is a parameterized test. See script named below for details.
$subtitle = "16 bubbles with size range 4-64";
$data = array(
    array('', 1, 1, 1, 2, 5, 3, 9,  4, 13),
    array('', 2, 1, 2, 2, 6, 3, 10, 4, 14),
    array('', 3, 1, 3, 2, 7, 3, 11, 4, 15),
    array('', 4, 1, 4, 2, 8, 3, 12, 4, 16),
);
$plot_area = array(0, 0, 5);
$bubbles_min_size = 4;
$bubbles_max_size = 64;
require 'bubbles1.php';
