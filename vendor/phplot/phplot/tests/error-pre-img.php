<?php

# PHPlot error test - fail before image creation.
require_once 'phplot.php';

$data = array(
  array('A',  1, 4),
  array('B',  2, 6),
  array('C',  3, 0),
);

$p = new Phplot\Phplot\phplot(400,300, NULL, '/no/such/input/file');
// Should never get here.
$p->SetTitle('Error test');
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');
$p->SetPlotType('lines');
$p->DrawGraph();
