<?php

# PHPlot Test: Area plot with data borders, and out-of-order data (master)
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'plot_type' => 'area',    # Plot type: [stacked][squared]area
  'mixed_data' => NULL,     # True to use out-of-order data array
  'draw_borders' => NULL,   # True to draw data borders
  'border_colors' => NULL,  # True to set border colors
  'x_axis' => NULL,         # Set to Y>0 to move the X axis up
        ), $tp);
require_once 'phplot.php';

# Extract to local vars:
extract($tp);
# Ignore data border colors if data borders are off:
if (empty($draw_borders)) // Unset or FALSE
  $border_colors = FALSE;

# Two possible data arrays: one sorted for 'area' plots
# with all Y(i)>=Y(i+1), and one out of order to show
# area overlaps with borders.
if (!empty($mixed_data)) {
# Unordered:
$data = array(
  array('1960', 100, 70, 60, 54, 16,  2),
  array('1970', 100, 80, 63, 54,  0, 20),
  array('1980', 100, 80, 54, 76, 27, 25),
  array('1990', 100, 95, 69,  0, 28, 10),
  array('2000',  72,100, 72, 54, 38,  5),
);
} else {
# Ordered:
$data = array(
  array('1960', 100, 70, 60, 54, 16,  2),
  array('1970', 100, 80, 63, 54, 20, 0),
  array('1980', 100, 80, 76, 54, 27, 25),
  array('1990', 100, 95, 69, 28, 10, 0),
  array('2000', 100, 72, 72, 54, 38,  5),
);

}

# Auto title:
$title = "$plot_type plot test, "
       . (empty($mixed_data) ? "ordered data values"
                        : "unordered values including Y<0") . "\n"
       . "Data borders are: " . (empty($draw_borders) ? "Off" : "On") . ", "
       . "Data border colors are: " . (empty($border_colors) ?
                           "Default" : "Set") . "\n"
       . "X Axis is: " . (!isset($x_axis)? "Default" : "at Y=$x_axis");

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetTitle($title);
$plot->SetPlotType($plot_type);
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
if (!empty($draw_borders)) $plot->SetDrawDataBorders(TRUE);
if (!empty($border_colors))
    $plot->SetDataBorderColors(array('green', 'yellow', 'blue', 'red'));
if (isset($x_axis)) $plot->SetXAxisPosition($x_axis);
$plot->DrawGraph();
