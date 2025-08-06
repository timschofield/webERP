<?php

# Testing phplot - data-data ordering
require_once 'phplot.php';

# 10 lines, one for each shape:
$data = array(

  array('',  1, 20),
  array('',  2,  2),
  array('',  3, 19),
  array('',  4,  3),
  array('',  6,  4),
  array('',  7, 17),
  array('',  8,  5),
  array('',  9, 16),
  array('',  5, 18),
  array('', 10,  6),

);

$p = new Phplot\Phplot\phplot();

$p->SetTitle('Out-of-order data-data points');
$p->SetPlotType('lines');
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetPlotAreaWorld(0, 0, 12, 25);
$p->SetXTickIncrement(1);
$p->SetYTickIncrement(1);

# We don't use the data labels (all set to '') so might as well turn them off:
$p->SetXDataLabelPos('none');

# Draw both grids:
$p->SetDrawXGrid(True);
$p->SetDrawYGrid(True); # The default


$p->DrawGraph();
