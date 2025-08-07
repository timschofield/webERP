<?php

# Testing PHPlot - Multi-plot (horizontal, vertical) - label and grid issues
# This shows an issue with label position parameters being calculated and
# stored back for the first plot, then seen as user settings for the second.
# Also this tests for the problem with the grid default (which is different
# for horizontal and vertical plots) sticking from the first plot to the second.
# Set $case=2 and include this script to add the Set*Pos() calls to reset
# the label position info.
require_once 'phplot.php';

if (!isset($case)) $case = 1;

$data = array(
    array('Monday', 10, 20, 30),
    array('Tuesday', 10, 20, 30),
    array('Wednesday', 10, 20, 30),
    array('Thursday', 10, 20, 30),
    array('Friday', 10, 20, 30),
);

// Common setup:
$plot = new Phplot\Phplot\phplot(460, 600);
$plot->SetTitle("Multiple Plots, horiz & vertical (case $case)");
$plot->SetPlotType('bars');
$plot->SetPrintImage(False);
$plot->SetDataValues($data);

// Plot #1:
$plot->SetPlotAreaPixels(NULL, 60, NULL, 300);
$plot->SetPlotAreaWorld();
$plot->SetDataType('text-data');
$plot->SetXTickPos('none');
$plot->DrawGraph();

// Plot #2:
$plot->SetPlotAreaPixels(NULL, 330, NULL, 570);
$plot->SetPlotAreaWorld();
$plot->SetDataType('text-data-yx');
if ($case == 2) { // Reset label positions
    $plot->SetXTickLabelPos('plotdown');
    $plot->SetXDataLabelPos('none');
    $plot->SetYTickLabelPos('none');
    $plot->SetYDataLabelPos('plotleft');
    $plot->SetXTickPos('plotdown');
}
$plot->DrawGraph();

// Finish:
$plot->PrintImage();
