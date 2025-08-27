<?php

# Testing PHPlot - Multi-plot with axis change - case 1
# Axis is calculated for first plot and becomes default for second plot.
# (This is documented behavior)
# Set $case=2 and include this script to reset axis positions before the
# second plot.
require_once 'phplot.php';

if (!isset($case)) $case = 1;

# Data array for plot #1:
# Results in Y axis on left side at X=10, X axis at bottom Y=10
$data1 = array(
    array('', 10, 10),
    array('', 11, 11),
    array('', 12, 12),
);

# Data array for plot #2:
# X axis should in the middle at Y=0
# Y axis should be on left at X=-20
# But instead they stick from plot #1 at Y=10, X=10.
$data2 = array(
    array('', -20, -10),
    array('',  -5,   2),
    array('',  30,  20),
);

// Common setup:
$plot = new Phplot\Phplot\phplot(460, 600);
$plot->SetTitle("Multiple Plots - axis position (case $case)\n"
              . "X and Y axis positions stick from upper plot");
$plot->SetPlotType('points');
$plot->SetDataType('data-data');
$plot->SetPrintImage(False);


// Plot #1:
$plot->SetPlotAreaPixels(NULL, 60, NULL, 300);
$plot->SetPlotAreaWorld();
$plot->SetDataValues($data1);
$plot->DrawGraph();

// Plot #2:
$plot->SetPlotAreaPixels(NULL, 330, NULL, 570);
$plot->SetPlotAreaWorld();
$plot->SetDataValues($data2);
if ($case == 2) {
    $plot->SetXAxisPosition(); // Reset to default
    $plot->SetYAxisPosition(); // Reset to default
}
$plot->DrawGraph();

// Finish:
$plot->PrintImage();
