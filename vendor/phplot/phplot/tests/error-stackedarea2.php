<?php

# Error test: stacked area plot with differing point counts
require_once 'phplot.php';
$data = array(
  array('A', 4, 3, 2, 1),
  array('B', 5, 3, 2),
  array('C', 6, 5, 4, 3),
  array('D', 7, 6, 5, 4, 12),
);
$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetTitle('Error test - Stacked Area plot with different point counts');
$plot->SetPlotType('stackedarea');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetYTickIncrement(1);
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->DrawGraph();
