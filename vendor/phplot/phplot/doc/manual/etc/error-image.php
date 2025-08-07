<?php
# Error Message Image example:
require_once 'phplot.php';
$data = array(
  array('A',  1, 4),
  array('B',  2, 6),
  array('C',  3, 0),
);
$p = new Phplot\Phplot\phplot(500, 200);
$p->SetPlotType('dots');  # Error!
$p->SetTitle('Error test');
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->DrawGraph();
