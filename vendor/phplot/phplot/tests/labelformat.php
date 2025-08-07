<?php

# Testing phplot: label format
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

$plot = new Phplot\Phplot\phplot();
$plot->SetPlotType('lines');
$plot->SetDataType('data-data');
$plot->SetDataValues($data);

# Main plot title:
$plot->SetTitle('Number formatted labels');

# Select a plot area and force ticks to nice values:
$plot->SetPlotAreaWorld(-1000, 0);

# Even though the data labels are empty, with numeric formatting they
# will be output as zeros unless we turn them off:
$plot->SetXDataLabelPos('none');

$plot->SetXTickIncrement(500);
$plot->SetXLabelType('data');
$plot->SetPrecisionX(0);

$plot->SetYTickIncrement(200000);
$plot->SetYLabelType('data');
$plot->SetPrecisionY(2);

$plot->SetDrawXGrid(False);
$plot->SetDrawYGrid(False);

# HACK
#$plot->decimal_point = ',';
#$plot->thousands_sep = '';
$plot->DrawGraph();
