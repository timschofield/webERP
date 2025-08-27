<?php

# PHPlot test: Bubbles plot, with image map area outlines shown.
# This produces a plot image with the areas that would be in an image map
# outlined. It does not produce HTML or an image map.
require_once 'phplot.php';

$data = array(
    array('A', 1,  10, 2,  20, 3 ),
    array('B', 2,  5, 18,  10, 9 ),
    array('C', 3,  2, 1,   12, 15 ),
    array('D', 4,  8, 6,   16, 20 ),
);
$data_type = 'data-data-xyz';
$plot_type = 'bubbles';
$title = "PHPlot Test: $plot_type plot with image map areas outlined";

# Callback for 'data_points': Outline the area
function store_map($im, $passthru, $shape, $row, $col, $xc, $yc, $diam)
{
    static $color = NULL;

    if (!isset($color)) $color = imagecolorallocate($im, 255, 0, 0);
    imageellipse($im, $xc, $yc, $diam, $diam, $color);
}

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetTitle($title);
$plot->SetImageBorderType('plain');
$plot->SetDataValues($data);
$plot->SetDataType($data_type);
$plot->SetPlotType($plot_type);
$plot->SetPlotAreaWorld(0, 0, 5, 25);
$plot->SetCallback('data_points', 'store_map');
$plot->DrawGraph();
