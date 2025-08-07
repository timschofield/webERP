<?php

# PHPlot test: Box plot, with image map area outlines shown.
# This produces a plot image with the areas that would be in an image map
# outlined. It does not produce HTML or an image map.
require_once 'phplot.php';

$data = array(
    #          X   Ymin  YQ1  Ymid  YQ3  Ymax   Outliers
    array('A', 1,   10,  12,   15,  17,   20),
    array('B', 2,    5,  10,   16,  20,   24,   2, 3, 4),
    array('C', 3,   12,  13,   14,  15,   16,  20),
    array('D', 4,   10,  11,   12,  13,   14),
    array('E', 5,   12,  12,   15,  18,   20),  // min = q1
    array('F', 6,   12,  14,   16,  18,   18),  // q3 = max
    array('G', 7,   10,  10,   15,  20,   20),  // min = q1 && q3 = max

);
$data_type = 'data-data';
$plot_type = 'boxes';
$title = "PHPlot Test: $plot_type plot with image map areas outlined";

# Callback for 'data_points': Outline the area
function store_map($im, $passthru, $shape, $row, $col, $x1, $y1, $x2, $y2)
{
    static $color = NULL;

    if (!isset($color)) $color = imagecolorallocate($im, 255, 0, 0);
    imagesetthickness($im, 3);
    imagerectangle($im, $x1, $y1, $x2, $y2, $color);
    imagesetthickness($im, 1);
}

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetTitle($title);
$plot->SetImageBorderType('plain');
$plot->SetDataValues($data);
$plot->SetDataType($data_type);
$plot->SetPlotType($plot_type);
$plot->SetPlotAreaWorld(0, 0, 8, 25);
$plot->SetCallback('data_points', 'store_map');
$plot->DrawGraph();
