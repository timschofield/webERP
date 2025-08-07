<?php

# Testing phplot - log axis
require_once 'phplot.php';

# Plot X*Y=10
$data = array();
for ($x = 1; $x <= 10; $x++) $data[] = array('', $x, 10.0/$x);

$p = new Phplot\Phplot\phplot(800, 600);

$p->SetDataType('data-data');
$p->SetDataValues($data);

# Titles:
$p->SetTitle("Axes Tests - Log/Log\nXY = 10");

# Axis types:
$p->SetXScaleType('log');
$p->SetYScaleType('log');

# We don't use the data labels (all set to '') so might as well turn them off:
$p->SetXDataLabelPos('none');

# Need to set area and ticks to get reasonable choices.
$p->SetPlotAreaWorld(1, 1, 11, 11);
$p->SetXTickIncrement(1);
$p->SetYTickIncrement(1);

# Draw both grids:
$p->SetDrawXGrid(True);
$p->SetDrawYGrid(True);

$p->SetPlotType('lines');
$p->DrawGraph();
