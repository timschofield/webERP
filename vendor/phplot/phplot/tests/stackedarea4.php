<?php

# Test: stacked area plot with 0 and <0 Y values.
require_once 'phplot.php';
$data = array(
  array('A', 10, 10, 10, 10, 10),
  array('B', 15, -10, 3, 5, 8),
  array('C', 0, 12, -6, 10, 4),
  array('D', 5, 5, 0, 5, 5),
);
$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetTitle('Stacked Area plot with 0 and <0 Y values');
$plot->SetPlotType('stackedarea');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetYTickIncrement(1);
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->DrawGraph();
