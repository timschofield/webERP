<?php

# Test: Area plot with non-decreasing, 0, and <0 Y values
require_once 'phplot.php';
$data = array(
  array('1960', 100, 70, 60, 54, 16,  2),
  array('1970', 100, 80, 63, 54,  0, 20),
  array('1980', 100, 80, 54, 67, 27, 25),
  array('1990', 100, 95, 69, -54, 28, 10),
  array('2000', 100, 72, 72, 54, 38,  5),
);
$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetTitle('Area plot with non-decreasing, 0, and <0 Y values');
$plot->SetPlotType('area');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
#$plot->SetYTickIncrement(1);
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->DrawGraph();
