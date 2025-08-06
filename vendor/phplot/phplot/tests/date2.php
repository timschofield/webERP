<?php

# Testing phplot - Date X axis labels, with data-data
require_once 'phplot.php';

#                   H  M  S  m  d   y
$base_time = mktime(0, 0, 12, 6, 30, 2001);
$one_day = 60 * 60 * 24;
$data = array(
  array(0, 0, 2),
  array(0, 0, 4),
  array(0, 0, 2),
  array(0, 0, 4),
  array(0, 0, 2),
  array(0, 0, 4),
);
# Store time values as both label and X value:
for ($i = 0; $i < 6; $i++)
   $data[$i][0] = $data[$i][1] = $base_time + $i * $one_day;

$p = new Phplot\Phplot\phplot(600,400);
$p->SetTitle('lines, date-data with date X labels');
$p->SetDataType('data-data');
$p->SetDataValues($data);

$p->SetXLabelType('time');
# For example:
# %Y-%m-%d  2004-12-31
# %b %Y     Dec 2004
# %b %d, %Y Dec 31, 2004
# %d %b     31 Dec
$p->SetXTimeFormat('%Y-%m-%d');

# Turn off X tick labels, use our data labels only:
$p->SetXTickLabelPos('none');

# Then we have to turn off the ticks too, because they aren't placed right:
$p->SetXTickPos('none');
$p->SetDrawXGrid(True);

$p->SetPlotType('lines');
$p->DrawGraph();
