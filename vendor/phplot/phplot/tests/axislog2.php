<?php

# Testing phplot - log axis
require_once 'phplot.php';

# Plot Y = 0.15e^x
$data = array();
for ($x = 0.25; $x <= 4; $x += 0.25) $data[] = array('', $x, 0.15 * exp($x));

$p = new Phplot\Phplot\phplot(800, 600);

$p->SetDataType('data-data');
$p->SetDataValues($data);

# Titles:
$p->SetTitle("Axes Tests - Semi-log\nY = 0.15 e^x");

# Axis types:
$p->SetYScaleType('log');

# We don't use the data labels (all set to '') so might as well turn them off:
$p->SetXDataLabelPos('none');

# Need to set area and ticks to get reasonable choices.
$p->SetPlotAreaWorld(.1, .1, 5, 12);
#$p->SetXTickIncrement(1);
#$p->SetYTickIncrement(1);

# Draw both grids:
$p->SetDrawXGrid(True);
$p->SetDrawYGrid(True);

$p->SetPlotType('lines');
$p->DrawGraph();
