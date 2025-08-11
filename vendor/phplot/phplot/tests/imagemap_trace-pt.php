<?php

# PHPlot test: Points plot, with image map areas marked
# This produces a plot image indicating the areas that would be in an image
# map. It does not produce HTML or an image map.
require_once 'phplot.php';

# These variables can be set by an external script:
# Plot type: points | linepoints
if (!isset($plot_type)) $plot_type = 'points';
# True for error plot, false or undefined for normal:
if (!isset($error_plot)) $error_plot = FALSE;

# Data for lines/points plots, normal or error:
if ($error_plot) {
    $data_type = 'data-data-error';
    $data = array(
      array('',  0,  0, 1, 1),
      array('',  1,  2, 1, 2),
      array('',  2,  4, 2, 1),
      array('',  3,  5, 1, 3),
      array('',  4,  5, 0, 1),
      array('',  5,  7, 1, 2),
      array('',  6,  6, 3, 1),
      array('',  7,  8, 1, 0),
    );
} else {
    $data_type = 'data-data';
    $data = array(
      array('',  0,  0,  1),
      array('',  1,  2,  1),
      array('',  2,  4,  2),
      array('',  3,  6,  3),
      array('',  4,  8,  5),
      array('',  5, 10,  8),
      array('',  6, 12, 12),
      array('',  7, 14, 20),
    );
}
$data_type = $error_plot ? 'data-data-error' : 'data-data';
$title = "PHPlot Test: $plot_type plot ("
      . ($error_plot ? 'error bars' : 'normal')
      . ') with image map areas marked';

# Callback for 'data_points': mark points with X
function store_map($im, $data, $shape, $row, $col, $x, $y)
{
    static $color = NULL;

    if (!isset($color)) $color = imagecolorallocate($im, 255, 0, 0);
    imageellipse($im, $x, $y, 12, 12, $color);
}


$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetTitle($title);
$plot->SetImageBorderType('plain');
$plot->SetDataValues($data);
$plot->SetDataType($data_type);
$plot->SetPlotType($plot_type);
$plot->SetCallback('data_points', 'store_map', $data);
$plot->DrawGraph();
