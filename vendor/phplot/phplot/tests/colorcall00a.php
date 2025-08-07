<?php

# Color callback - bar plot with callback
# See the script named below for details.
$plot_type = 'bars';
function pick_color($unused_img, $unused_passthru, $row, $col)
{
    return $row + $col;
}
require 'colorcall00.php';
