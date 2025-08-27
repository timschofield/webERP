<?php

# Testing phplot - Wrong ticks on X and Y
require_once 'phplot.php';

$data = array(
  array('', 0,  0),
  array('', 95,  50),
);

$p = new Phplot\Phplot\phplot();
$p->SetTitle('Tick Count Tests');
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');
$p->SetXTitle('X');
$p->SetYTitle('Y');

$p->SetPlotAreaWorld(0, 0, 98, 55);
$p->SetXTickIncrement(10);
$p->SetYTickIncrement(10);

#$p->SetSkipTopTick(False);

# Draw both grids:
$p->SetDrawXGrid(True);
$p->SetDrawYGrid(True);

# Axes on all sides:
$p->SetXTickPos('both');
$p->SetXTickLabelPos('both');
$p->SetYTickPos('both');
$p->SetYTickLabelPos('both');
$p->SetPlotBorderType('full');

$p->SetPlotType('lines');
$p->DrawGraph();
