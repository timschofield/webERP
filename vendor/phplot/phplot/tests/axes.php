<?php

# Testing phplot - axes controls
require_once 'phplot.php';

# This is Y = X^3/8 for X = [-4:4]
$data = array(
  array('', -4, -8),
  array('', -3, -3.375),
  array('', -2, -1),
  array('', -1, -0.125),
  array('', 0, 0),
  array('', 1, 0.125),
  array('', 2, 1),
  array('', 3, 3.375),
  array('', 4, 8),
);

$p = new Phplot\Phplot\phplot(400, 300);

$p->SetDataType('data-data');
$p->SetDataValues($data);

# Titles:
$p->SetTitle("Axes Tests\nForce X axis to Y=-3");

# Axis positions:
$p->SetXAxisPosition(-3);
$p->SetYAxisPosition(0);

# We don't use the data labels (all set to '') so might as well turn them off:
$p->SetXDataLabelPos('none');

# Need to set area and ticks to get reasonable choices.
$p->SetPlotAreaWorld(-5, -10, 5, 10);
$p->SetXTickIncrement(1);
$p->SetYTickIncrement(1);

# Draw both grids:
$p->SetDrawXGrid(True);
$p->SetDrawYGrid(True);

$p->SetPlotType('lines');
$p->DrawGraph();
