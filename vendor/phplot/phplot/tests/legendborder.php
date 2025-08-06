<?php

# PHPlot test: Legend border control (PHPlot > 6.2.0)
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Legend Border Control',
  'suffix' => 'Defaults: border on, black',  # Title part 2
  'draw_border' => NULL,            # Draw border? True False NULL (to skip)
  'border_color' => NULL,           # Legend border color if set
  'grid_color' => NULL,             # Grid color
  'legend_bg_color' => NULL,        # Legend background color
        ), $tp);
require_once 'phplot.php';
# Notes on above parameters:
# + grid_color is default for legend border_color. Setting both, border_color
#   should override grid_color with grid_color used for axis lines.
# + legend background color should be independent of border (which is drawn
#   after).


# Function to check for required PHPlot method:
# To work with the test driver, it must write to stdout and exit(2) if skipping.
function checkfor($method_name)
{
    if (method_exists('Phplot\Phplot\phplot', $method_name)) return;
    echo "Skipping test because it requires '$method_name'\n";
    exit(2);
}

# Extract all test parameters as local variables:
extract($tp);

$data = array(
    array('A', 20, 30, 10, 50, 10),
    array('B', 10, 30, 20, 40, 10),
    array('C', 40, 30, 30, 30, 10),
    array('D', 50, 30, 40, 20, 10),
);

# Fixed setup:
$plot = new Phplot\Phplot\phplot(800, 800);
for ($i = 0; $i < 5; $i++)
    $plot->SetLegend("Test Case # $i Results");
$plot->SetTitle($title . "\n" . $suffix);
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetPlotType('bars');

# Variable setup:
if (isset($border_color)) {
    checkfor('SetLegendBorderColor');
    $plot->SetLegendBorderColor($border_color);
}
if (isset($draw_border)) {
    checkfor('SetDrawLegendBorder');
    $plot->SetDrawLegendBorder($draw_border);
}
if (isset($grid_color)) $plot->SetGridColor($grid_color);
if (isset($legend_bg_color)) $plot->SetLegendBgColor($legend_bg_color);

$plot->DrawGraph();
