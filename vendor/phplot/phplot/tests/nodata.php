<?php

# PHPlot error test - Charts with bad data array, bug report from Drupal
# The bug was that PHPlot detects missing or empty data array, but if the
# array has rows and the rows have no Y values, it fails poorly.
# All of these cases except 0 should produce *empty* plots with no error
# reported. (Empty = axes, titles, but nothing inside the graph)
# This is a parameterized test. Set $case before calling.
#   case 0: Baseline, no error
#   case 1: Pie chart, text-data, data array has 1 row with only label
#   case 2: Pie chart, text-data, data array has 1 row with label and empty Ys
#   case 3: Line plot, data-data, no valid Y.
#   case 4: Area plot, text-data, no valid Y.
#   case 5: Points error plot, data-data-error, no valid Y/+err/-err.
require_once 'phplot.php';
if (!isset($case)) $case = 0;

switch ($case) {
case 1:
    $plot_type = 'pie';
    $data_type = 'text-data';
    $data = array( array('a'));
    $title = "Case $case ($plot_type, no Y entries in array)";
    break;

case 2:
    $plot_type = 'pie';
    $data_type = 'text-data';
    $data = array( array('a', '', ''));
    $title = "Case $case ($plot_type, all Y values are empty)";
    break;

case 3:
    $plot_type = 'lines';
    $data_type = 'data-data';
    $data = array( array('a', 1, ''), array('b', 2));
    $title = "Case $case ($plot_type, all Y values are empty)";
    break;

case 4:
    $plot_type = 'area';
    $data_type = 'text-data';
    $data = array( array('a', '', ''), array('b', '', ''));
    $title = "Case $case ($plot_type, all Y values are empty)";
    break;

case 5:
    $plot_type = 'points';
    $data_type = 'data-data-error';
    $data = array( array('a', 1));
    $title = "Case $case ($plot_type $data_type, no Y/err values)";
    break;

default: # Case 0 - baseline
    $plot_type = 'pie';
    $data_type = 'text-data';
    $data = array( array('a', 100, 200, 300));
    $title = 'Baseline';
    break;
}

$plot = new Phplot\Phplot\phplot(800,600);
$plot->SetTitle("Bad Data Array - $title");
$plot->SetDataType($data_type);
$plot->SetPlotType($plot_type);
$plot->SetDataValues($data);
$plot->DrawGraph();
