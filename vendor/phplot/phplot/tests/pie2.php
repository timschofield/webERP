<?php

# PHPlot Example: Pie/text-data
require_once 'phplot.php';

$data = array(
  array('', 100, 100, 200, 100),
  array('', 150, 100, 150, 100),
);

$plot = new Phplot\Phplot\phplot(800,600);
$plot->SetImageBorderType('plain');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetPlotType('pie');
$plot->DrawGraph();
