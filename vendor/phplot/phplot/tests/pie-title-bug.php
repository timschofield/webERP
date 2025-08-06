<?php

# PHPlot Test: bug 2914403 Pie + X/Y titles: Undefined property error
# The bug was that X and Y titles were supposed to be ignored, but if
# set then an undefined property error occurred.
require_once 'phplot.php';

$data = array(
  array('', 50),
  array('', 30),
  array('', 20),
);

$plot = new Phplot\Phplot\phplot(800,600);
$plot->SetTitle('Pie + X/Y Title Bug');
$plot->SetXTitle('Ignored X title');
$plot->SetYTitle('Ignored Y title');
$plot->SetImageBorderType('plain');
$plot->SetDataType('text-data-single');
$plot->SetDataValues($data);
$plot->SetPlotType('pie');
$plot->DrawGraph();
