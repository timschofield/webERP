<?php

# PHPlot test: OHLC/Candlestick plot, with image map area outlines shown.
# This produces a plot image with the areas that would be in an image map
# outlined. It does not produce HTML or an image map.
require_once 'phplot.php';

# This variable can be set by an external script:
# Plot type: ohlc | candlesticks | candlesticks2
if (!isset($plot_type)) $plot_type = 'candlesticks2';

# Data for OHLC/candlesticks plot (text-data, no X values):
$data = array(
//              Open, High, Low, Close
  array('Mon',  10,   12,    9,  11),
  array('Tue',  11,   18,   11,  15),
  array('Wed',  15,   15,   10,  15),
  array('Thu',  15,   16,    8,  10),
  array('Fri',  10,   17,   10,  16),

  array('Mon',  16,   19,   15,  18),
  array('Tue',  18,   20,   10,  10),
  array('Wed',  10,   10,    5,   6),
  array('Thu',   6,    6,    6,   6),
  array('Fri',   6,   10,    4,   6),
);
$data_type = 'text-data';
$title = "PHPlot Test: $plot_type plot with image maps areas outlined";

# Callback for 'data_points': Outline the area
function store_map($im, $passthru, $shape, $row, $unused, $x1, $y1, $x2, $y2)
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
$plot->SetCallback('data_points', 'store_map');
$plot->DrawGraph();
