<?php

# PHPlot test: Bubble plot - regressive case of no Z depth
# This is a parameterized test. See script named below for details.
$subtitle = "All Z values the same";
$data = array(
    array('', 1, 1, 1, 2, 1, 3, 1, 4, 1),
    array('', 2, 1, 1, 2, 1, 3, 1, 4, 1),
    array('', 3, 1, 1, 2, 1, 3, 1, 4, 1),
    array('', 4, 1, 1, 2, 1, 3, 1, 4, 1),
);
$plot_area = array(0, 0, 5, 5);
require 'bubbles1.php';
