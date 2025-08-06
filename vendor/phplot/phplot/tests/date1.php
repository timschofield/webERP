<?php
# Testing phplot - Date X axis labels
require_once 'phplot.php';

#                   H  M  S  m  d   y
$base_time = mktime(0, 0, 0, 6, 30, 2001);
$one_day = 60 * 60 * 24;
$data = array(
  array(0,  2),
  array(0,  4),
  array(0,  6),
  array(0,  8),
  array(0,  7),
  array(0,  5),
);
for ($i = 0; $i < 6; $i++) $data[$i][0] = $base_time + $i * $one_day;

$p = new Phplot\Phplot\phplot(600,400);
$p->SetTitle('bars, text-data with date X labels');
$p->SetDataType('text-data');
$p->SetDataValues($data);

$p->SetXLabelType('time');
# For example:
# %Y-%m-%d  2004-12-31
# %b %Y     Dec 2004
# %b %d, %Y Dec 31, 2004
# %d %b     31 Dec
$p->SetXTimeFormat('%d %b');

# Turn off X tick labels, use our data labels only:
$p->SetXTickLabelPos('none');
# Then we have to turn off the ticks too, because they aren't placed right:
$p->SetXTickPos('none');
$p->SetDrawXGrid(True);

$p->SetPlotType('bars');
$p->DrawGraph();
