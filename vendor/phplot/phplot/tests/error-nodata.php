<?php

# PHPlot error test: bug 1446523, part 2 only: No data array
require_once 'phplot.php';

$data = array(
  array('',  0,   0),
  array('',  1,   1),
  array('',  2,   8),
);

$p = new Phplot\Phplot\phplot();
$p->SetTitle('Bugcheck: No data');
$p->SetDataType('data-data');

# DON'T:
#$p->SetDataValues($data);

$p->SetFont('x_label', 2);
$p->SetFont('y_label', 2);

$p->SetPlotType('lines');
$p->DrawGraph();
