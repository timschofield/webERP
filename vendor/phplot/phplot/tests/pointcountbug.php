<?php

# Bug 2963757 - point_counts undefined error at 5.1.0
# The bug is triggered with same-size point shape and point size arrays.
require_once 'phplot.php';
# Define $case and call this script for test cases. See below.
if (empty($case)) $case = 0;
switch ($case) {
  case 0:  # Case: 1 point_size, 1 point_shape
    $point_shapes = 'dot';
    $point_sizes = 12;
    break;
  case 1:  # Case: N>1 point_size, N point_shape
    $point_shapes = array('dot', 'box');
    $point_sizes = array(6, 12);
    break;
  case 2:  # Case: N point_size, >N point_shape
    $point_shapes = array('dot', 'box');
    $point_sizes = 12;
    break;
  case 3:  # Case: N point_size, <N point_shape
    $point_shapes = 'dot';
    $point_sizes = array(6, 12);
    break;
}


$data = array(
  array('', 0, 1, 2, 3),
  array('', 1, 2, 3, 4),
  array('', 2, 3, 4, 5),
);
$p = new Phplot\Phplot\phplot(600, 400);
$p->SetTitle("Point Sizes/Shapes (Bug 2963757) - case $case");
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetLineWidths(3);
$p->SetLineStyles('solid');
$p->SetPointShapes($point_shapes);
$p->SetPointSizes($point_sizes);
$p->SetPlotType('linepoints');
$p->DrawGraph();
