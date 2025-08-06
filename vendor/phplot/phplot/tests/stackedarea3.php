<?php

# Test: stacked area plot with raised X axis
require_once 'phplot.php';
$data = array(
  array('A', 1, 1, 1, 1, 1),
  array('B', 2, 2, 2, 2, 2),
  array('C', 4, 3, 2, 1, 1),
  array('D', 5, 2, 5, 2, 5),
);
$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetTitle('Stacked Area plot with X axis raised to 3');
$plot->SetPlotType('stackedarea');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetXAxisPosition(3);
$plot->SetYTickIncrement(1);
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->DrawGraph();
