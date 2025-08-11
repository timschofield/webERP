<?php

# Testing PHPlot: Set/Reset parameters - case 2
# This produces 2 plots: one with parameters set, and
# the second after reset.
# (resets2 tests tick increment; resets3 tests num ticks. These
# interact so they can't be done in one test.)
require_once 'phplot.php';

/*
Note: The lower plot's Y axis will look strange. This is because PHPlot
calculates and sets the top and bottom of the Y range at the first plot,
and divides it into the exact number of intervals given. It does not
attempt to round this off to 'nice' numbers, because of the fixed number of
tick intervals.

The second plot removes the fixed number of intervals, but the range is
'sticky' so it persists from the first plot. In particular, PHPlot will not
adjust the range of the second plot to make a tick mark fall on 0. It will
start a tick mark at the bottom of the range. This is the expected
behavior, even though it results in unusual numbers along the Y axis.

In a real application, you would probably do one of the following to make
more reasonable tick mark values:
    SetPlotAreaWorld() - have PHPlot re-calculate the range and tick values
Or
    SetYTickAnchor(0) - force a tick at Y=0; this moves all the ticks to
whole numbers.

*/

$data = array();
# This is a cubic equation with roots at -8, 2, 10
for ($x = -10; $x <= 10; $x++)
    $data[] = array('', $x, ($x + 8) * ($x - 2) * ($x - 10));

$p = new Phplot\Phplot\phplot(400, 800);
$p->SetPrintImage(FALSE);
$p->SetPlotBorderType('full');

$p->SetTitle("Set/Reset Parameters Test (2)\n"
   . "Top: Parameters Set\n"
   . "Bottom: Parameters Reset");
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetPlotType('lines');
$p->SetLegend('Y = F(X)');
$p->SetLegendPixels(100, 200);
$p->SetNumXTicks(5);
$p->SetNumYTicks(8);
$p->SetXTitle('X Axis with 5 ticks');
$p->SetYTitle('Y Axis with 8 ticks');
$p->SetXAxisPosition(-228);
$p->SetYAxisPosition(7);
$p->SetPlotAreaPixels(70, 80, 380, 400);
$p->DrawGraph();

$p->SetLegendPixels();
$p->SetNumXTicks();
$p->SetNumYTicks();
$p->SetXTitle('X Axis');
$p->SetYTitle('Y Axis');
$p->SetXAxisPosition();
$p->SetYAxisPosition();
$p->SetPlotAreaPixels(70, 450, 380, 750);
$p->DrawGraph();

$p->PrintImage();
