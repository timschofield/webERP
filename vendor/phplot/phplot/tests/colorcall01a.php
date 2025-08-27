<?php

# Color callback - lines plot with callback
# See the script named below for details.
$plot_type = 'lines';
function pick_color($unused_img, $unused_passthru, $row, $col)
{
    return $row + $col;
}
require 'colorcall00.php';
