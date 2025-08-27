<?php

# Color callback - data-data-error linepoints plot with callback, variation
# See the script named below for details.
$plot_type = 'linepoints';
$subtitle = '- dot vs line color';
function pick_color($unused_img, $unused_passthru, $row, $col, $opt = 0)
{
    if ($opt == 1) return $col;
    return $row + $col;
}
require 'colorcall10.php';
