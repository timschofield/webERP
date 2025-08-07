<?php

# Color callback - data-data-error points plot with callback
# See the script named below for details.
$plot_type = 'points';
function pick_color($unused_img, $unused_passthru, $row, $col)
{
    return $row + $col;
}
require 'colorcall10.php';
