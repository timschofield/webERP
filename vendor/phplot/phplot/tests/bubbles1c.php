<?php

# PHPlot test: Bubble plot - Scattergram with random points and values
# This is a parameterized test. See script named below for details.
$subtitle = "Random Z values";
$n_p = 100; // Number of points
$max_x = 20; // X range
$max_y = 100; // Y range
$max_z = 100; // Z range

$data = array();
mt_srand(1);
for ($i = 0; $i < $n_p; $i++) {
    $data[] = array('', mt_rand(0, $max_x-1),
        mt_rand(0, $max_y-1), mt_rand(0, $max_z-1));
}
$plot_area = array(0, 0,$max_x, $max_y);
require 'bubbles1.php';
