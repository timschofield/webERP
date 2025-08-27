<?php

# PHPlot error test - bad option checking (bug in PHPlot<=5.0.4, inadequate check)
require_once 'phplot.php';

$data = array(
  array('A',  0,  1),
  array('B',  1,  2),
);

$p = new Phplot\Phplot\phplot(400,300);
$p->SetTitle('Error Tests');
$p->SetDataValues($data);
$p->SetDataType('data-data');
$p->SetPlotType('q');
$p->DrawGraph();
