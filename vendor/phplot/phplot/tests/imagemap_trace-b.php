<?php

# PHPlot test: Bar chart, with image map area outlines shown.
# This produces a plot image with the areas that would be in an image map
# outlined. It does not produce HTML or an image map.
require_once 'phplot.php';

# These variables can be set by an external script:
# Plot type: bars | stackedbars
if (!isset($plot_type)) $plot_type = 'bars';
# True for horizontal, false or undefined for vertical:
if (!isset($horizontal)) $horizontal = FALSE;

# Data for bar chart:
$data = array(
    array('1950', 40, 95, 20),
    array('1960', 45, 85, 30),
    array('1970', 50, 80, 40),
    array('1980', 48, 77, 50),
    array('1990', 38, 72, 60),
    array('2000', 35, 68, 70),
    array('2010', 30, 67, 80),
);
$data_type = $horizontal? 'text-data-yx' : 'text-data';
$title = "PHPlot Test: $plot_type plot ("
      . ($horizontal ? 'horiz' : 'vert')
      . ') with image map areas outlined';

# Callback for 'data_points': Outline the area
function store_map($im, $passthru, $shape, $row, $col, $x1, $y1, $x2, $y2)
{
    static $color = NULL;

    if (!isset($color)) $color = imagecolorallocate($im, 255, 0, 0);

    imagesetthickness($im, 3);
    imagerectangle($im, (int)$x1, (int)$y1, (int)$x2, (int)$y2, $color);
    imagesetthickness($im, 1);
}

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetTitle($title);
$plot->SetImageBorderType('plain');
$plot->SetDataValues($data);
$plot->SetDataType($data_type);
$plot->SetPlotType($plot_type);
$plot->SetCallback('data_points', 'store_map');
$plot->DrawGraph();
