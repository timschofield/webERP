<?php

# PHPlot test: single row stacked bars should look like regular bars?
require_once 'phplot.php';

$data = array(
  array('Jan', 40), array('Feb', 90),
  array('Mar', 50), array('Apr', 40),
  array('May', 75), array('Jun', 45),
  array('Jul', 40), array('Aug', 35),
  array('Sep', 50), array('Oct', 45),
  array('Nov', 35), array('Dec', 40),
);

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain');

$plot->SetPlotType('stackedbars');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetTitle("Single-dataset stackedbars\nCompare to unstacked");

$plot->SetShading(0);

$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');

# Testing:
#$plot->group_frac_width = 1.0;
#$plot->bar_width_adjust=0.5;

$plot->DrawGraph();
