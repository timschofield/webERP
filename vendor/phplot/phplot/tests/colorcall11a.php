<?php

# Color callback - data-data-error linepoints plot with callback
# See the script named below for details.
$plot_type = 'linepoints';
function pick_color($unused_img, $unused_passthru, $row, $col)
{
    return $row + $col;
}
require 'colorcall10.php';
