<?php

# Testing: Image border reset to none, label format reset to default, baseline.
# This runs stand-alone, or called with $test_resets=True to test
# resetting attributes.
# Reference: Bug report 2839457
require_once 'phplot.php';

$data = array(
  array('', -1000,    1000),
  array('',  -500,   23456),
  array('',     0,     800),
  array('',   500,  234100),
  array('',  1000, 1234567),
  array('',  1500,  100000),
  array('',  2000, 1901000),
  array('',  2500,  999999),
);

$title = "Test Attribute Resets:\n";
if (empty($test_resets)) {
  $title .= 'Baseline - red border, formatted labels';
} else {
  $title .= 'Reset to no border, no label formatting';
}

$plot = new Phplot\Phplot\phplot(400, 400);
$plot->SetTitle($title);
$plot->SetPlotType('lines');
$plot->SetDataType('data-data');
$plot->SetDataValues($data);
$plot->SetPlotAreaWorld(-1000, 0);
$plot->SetXDataLabelPos('none');
$plot->SetXTickIncrement(500);
$plot->SetXLabelType('data', 0, '', 'M');
$plot->SetYTickIncrement(200000);
$plot->SetYLabelType('data', 2);
$plot->SetImageBorderType('raised');
$plot->SetImageBorderColor('red');
$plot->SetDrawXGrid(False);
$plot->SetDrawYGrid(False);

# Set $test_resets=True and include this file to test resets:
if (!empty($test_resets)) {
  # Reset to no border:
  $plot->SetImageBorderType('none');
  # Reset X to no formatting using empty string:
  $plot->SetXLabelType('');
  # Reset Y to no formatting using no argument:
  $plot->SetYLabelType();
}

$plot->DrawGraph();
