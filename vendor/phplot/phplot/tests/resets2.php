<?php

# Testing PHPlot: Set/Reset parameters - case 1
# This produces 2 plots: one with parameters set, and
# the second after reset.
# (resets2 tests tick increment; resets3 tests num ticks. These
# interact so they can't be done in one test.)
require_once 'phplot.php';

$data = array();
# This is a cubic equation with roots at -8, 2, 10
for ($x = -10; $x <= 10; $x++)
    $data[] = array('', $x, ($x + 8) * ($x - 2) * ($x - 10));

$p = new Phplot\Phplot\phplot(400, 800);
$p->SetPrintImage(FALSE);
$p->SetPlotBorderType('full');

$p->SetTitle("Set/Reset Parameters Test (1)\n"
   . "Top: Parameters Set\n"
   . "Bottom: Parameters Reset");
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetPlotType('lines');
$p->SetLegend('Y = F(X)');
$p->SetLegendPixels(100, 200);
$p->SetXTickIncrement(5.0);
$p->SetYTickIncrement(50.0);
$p->SetXTitle('X Axis');
$p->SetYTitle('Y Axis');
$p->SetXAxisPosition(-228);
$p->SetYAxisPosition(7);
$p->SetPlotAreaPixels(70, 80, 380, 400);
$p->DrawGraph();

$p->SetLegendPixels();
$p->SetXTickIncrement();
$p->SetYTickIncrement();
$p->SetXAxisPosition();
$p->SetYAxisPosition();
$p->SetPlotAreaPixels(70, 450, 380, 750);
$p->DrawGraph();

$p->PrintImage();
