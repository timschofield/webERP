<?php
# Testing phplot - Wrong ticks on X and Y
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Tick Count:',
  'xmin' => 0,              # SetPlotAreaWorld: x min
  'xmax' => 98,             # SetPlotAreaWorld: x max
  'ymin' => 0,              # SetPlotAreaWorld: y min
  'ymax' => 55,             # SetPlotAreaWorld: y max
  'xti' => 10,              # XTickIncrement
  'yti' => 10,              # YTickIncrement
        ), $tp);

require_once 'phplot.php';

# The data points don't matter at all. The range is set with SetPlotAreaWorld.
$data = array(
  array('', 0,  0),
  array('', 1,  1),
);

$p = new Phplot\Phplot\phplot();
$subtitle = " World: ({$tp['xmin']}, {$tp['ymin']}) :"
          . " ({$tp['xmax']}, {$tp['ymax']})"
          . " Tickstep: ({$tp['xti']}, {$tp['yti']})";

$p->SetTitle($tp['title'] . $subtitle);
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');
$p->SetXTitle('X');
$p->SetYTitle('Y');

$p->SetPlotAreaWorld($tp['xmin'], $tp['ymin'], $tp['xmax'], $tp['ymax']);
$p->SetXTickIncrement($tp['xti']);
$p->SetYTickIncrement($tp['yti']);

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
