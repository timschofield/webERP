<?php

# PHPlot test: Plot range test, implied independent variable (bar plot)
#
# The purpose of this test is just to see how the X axis adapts to
# different numbers of bars. (Y axis, for horizontal plots)
#
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'nx' => 1,               # Number of independent variable data points
  'ny' => 1,               # Number of data sets (dependent variables)
  'horizontal' => FALSE,   # True for horizontal bars, false for vertical
        ), $tp);
require_once 'phplot.php';

# Extract all test parameters as local variables:
extract($tp);

# Horizontal vs vertical:
if ($horizontal) {
    $data_type = 'text-data-yx';
    $title = "Bar Chart X Axis Range Test: $nx Bars, $ny Data Sets";
} else {
    $data_type = 'text-data';
    $title = "Horizontal Bar Chart Y Axis Range Test: $nx Bars, $ny Data Sets";
}

# Build a data array:
$data = array();
for ($x = 0; $x < $nx; $x++) {
    $row = array("=$x=");
    for ($y = 0; $y < $ny; $y++) {
        $row[] = $x + $y + 10;
    }
    $data[] = $row;
}

$p = new Phplot\Phplot\phplot(800, 800);
$p->SetTitle($title);
$p->SetDataType($data_type);
$p->SetDataValues($data);
$p->SetPlotType('bars');
$p->SetShading(0);
# Note: In a typical bar plot, X axis tick labels would be off and only
# data labels would be shown, but for test purposes both are on here, with
# tick labels on opposite side.
if ($horizontal) {
    $p->SetYTickPos('both');
    $p->SetYTickLabelPos('plotright');
    $p->SetYDataLabelPos('plotleft');
} else {
    $p->SetXTickPos('both');
    $p->SetXTickLabelPos('plotup');
    $p->SetXDataLabelPos('plotdown');
}
$p->SetPlotBorderType('full');
$p->DrawGraph();
