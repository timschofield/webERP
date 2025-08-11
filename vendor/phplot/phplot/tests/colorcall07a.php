<?php

# Color callback - horizontal bar plot with color callback
# See the script named below for details.
$plot_type = 'bars';
$data_type = 'text-data-yx';
function pick_color($unused_img, $unused_passthru, $row, $col)
{
    return $row + $col;
}
require 'colorcall00.php';
