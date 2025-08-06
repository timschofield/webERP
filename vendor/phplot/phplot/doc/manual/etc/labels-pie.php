<?php
# Pie chart with labels, for manual.
require_once 'phplot.php';

$data = array(
  array('', 10),
  array('', 40),
  array('', 50),
);

$p = new Phplot\Phplot\phplot(400,300);
$p->SetDataType('text-data-single');
$p->SetDataValues($data);
$p->SetPlotType('pie');
$p->SetImageBorderType('plain');
$p->DrawGraph();
