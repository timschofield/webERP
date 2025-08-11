<?php

# Testing phplot - All line colors
require_once 'phplot.php';

$data = array();
for ($i = 1; $i <= 20; $i++) {
  $row = array('', $i);
  for ($line = 1; $line <= 16; $line++) $row[] = $line + $i;
  $data[] = $row;
}

$p = new Phplot\Phplot\phplot();
$p->SetTitle('Default Plot Line Colors');
$p->SetDataType('data-data');
$p->SetDataValues($data);

$p->SetPlotAreaWorld(0, 0);
$p->SetXTickIncrement(1);
$p->SetYTickIncrement(1);

# Don't use dashes for 3rd line:
$p->SetLineStyles('solid');

# Make the lines thicker:
$p->SetLineWidths(3);

# No grids:
$p->SetDrawXGrid(False);
$p->SetDrawYGrid(False);

#$p->SetBackgroundColor('navy');
$p->SetPlotType('lines');
$p->DrawGraph();
