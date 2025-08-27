<?php
# Testing phplot - Suppress axis lines - baseline/master
# Test case is selected by $case.
#  case 0 : Baseline, axis lines visible
#  case 1 : Hide X axis only
#  case 2 : Hide Y axis only
#  case 3 : Hide X and Y axis lines
#  case 4 : Hide X and Y axis lines and maximize plot area usage.

require_once 'phplot.php';

if (!isset($case)) $case = 0;

$data = array(
  array('A', 1, 2),
  array('B', 2, 3),
  array('C', 3, 4),
  array('D', 4, 3),
  array('E', 3, 1),
);

$p = new Phplot\Phplot\phplot();
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType('bars');
$p->SetTitle("Test axis suppression - case $case");
$p->SetXTickPos('none');
$p->SetXTickLabelPos('none');
$p->SetXDataLabelPos('none');
$p->SetYTickPos('none');
$p->SetYTickLabelPos('none');
$p->SetPlotBorderType('none');
$p->SetDrawXGrid(False);
$p->SetDrawYGrid(False);
if ($case == 1 || $case == 3 || $case == 4) $p->SetDrawXAxis(FALSE);
if ($case == 2 || $case == 3 || $case == 4) $p->SetDrawYAxis(FALSE);
if ($case == 4) $p->SetMarginsPixels(2, 2, 2, 2); // L R T B
$p->DrawGraph();
