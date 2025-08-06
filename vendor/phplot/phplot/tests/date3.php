<?php

# PHPlot test: Date X axis labels, with data-data, data labels with suppression
# This is a new feature added to PHPlot after 5.0rc3, to ignore blanks
# as date/time labels.
require_once 'phplot.php';

#                   H  M  S   m  d   y
$base_time = mktime(0, 0, 12, 6, 30, 2004);
$interval = 60 * 60;
$data = array();
# This is a quadratic from 0,0 to 50,20 to 100,0:
for ($i = 0; $i <= 100; $i++) {
  if ($i % 24 == 0)
    $t = $base_time + $i * $interval;
  else
    $t = '';
  $data[] = array($t, $i,  $i * (0.8 - 0.008 * $i));
}

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle('Date X labels - 8 hour ticks, 24 hour labels');
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetPlotAreaWorld(0, 0, 100, 20);

$p->SetXLabelType('time');
# For example:
# %Y-%m-%d  2004-12-31
# %b %Y     Dec 2004
# %b %d, %Y Dec 31, 2004
# %d %b     31 Dec
$p->SetXTimeFormat('%m/%d %H:%M');

# Turn off X tick labels, use our data labels only:
$p->SetXTickLabelPos('none');
# But we can use the tick marks themselves if we make them line up right.
$p->SetXTickIncrement(8);
$p->SetDrawXGrid(True);

$p->SetPlotType('lines');
$p->DrawGraph();
