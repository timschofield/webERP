<?php

# Testing phplot - Automatic margin calculation - baseline
# Margin is affected by:
#   Title, X Title, Y Title : empty or 1 or multi-line, X/Y title position
#   Data or Tick labels : contents, position (0-4 sides)
#   Tick length and tick position
#
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Auto-margin Test',  # First or only line
  'suffix' => ' - baseline, bottom/left titles/ticks',  # Title part 2
  'do_title' => True,           # True to display the main title
  'x_title_pos' => 'plotdown',  # X title position: plotdown plotup both none
  'y_title_pos' => 'plotleft',  # Y title position: plotleft plotright both none
  'xticklabel' => 'plotdown', # X tick & label position: none|both|plotup|plotdown|xaxis
  'xtick' => NULL,        # X tick override, if different from xticklabel
  'yticklabel' => 'plotleft', # Y tick & label position: none|both|plotleft|plotright|yaxis
  'ytick' => NULL,        # Y tick override, if different from yticklabel
  'xticklen' => NULL,     # X tick length (outside graph)
  'yticklen' => NULL,     # Y tick length (outside graph)
  'xdatalabel' => NULL,   # X data label position: NULL|plotdown|plotup|both
                          #  Note: If not-NULL, x tick labels are off
  'xlt0' => False,        # True to have some negative (x < 0) X data and move Y axis to 0
  'ylt0' => False,        # True to have some negative (y < 0) Y data
  'margins' => NULL,      # If set, array of 4 margins (t r b l), else automatic
        ), $tp);
require_once 'phplot.php';

# Uncomment this and the callback setup below for tracing:
#require_once 'debug.php';

$data = array(
  array('Jan', 1, 0),
  array('Feb', 2, 100),
  array('Mar', 3, 10),
  array('Apr', 4, 90),
  array('May', 5, 20),
  array('Jun', 6, 80),
  array('Jul', 7, 30),
  array('Aug', 8, 70),
  array('Sep', 9, 40),
  array('Oct',10, 60),
  array('Nov',11, 50),
  array('Dec',12, 50),
);
# Data range:
$x0 = 0;
$x1 = 13;
$y0 = 0;
$y1 = 100;
# Make a negative Y value:
if ($tp['ylt0']) {
  $data[11][2] = -10;
  $y0 = -10;
}
# Make a negative X value:
if ($tp['xlt0']) {
  array_unshift($data, array('Base', -1, 20));
  $x0 = -2;
}

$x_title = 'X Axis Title';
$y_title = 'Y Axis Title';

$p = new Phplot\Phplot\phplot(800,600);
# Uncomment this and the require above for tracing:
#$p->SetCallback('debug_scale', 'debug_handler');
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetPlotBorderType('full');
$p->SetPlotAreaWorld($x0, $y0, $x1, $y1);
if ($tp['do_title']) $p->SetTitle($tp['title'] . $tp['suffix']);
$p->SetXDataLabelPos('none');
$p->SetXTitle($x_title, $tp['x_title_pos']);
$p->SetYTitle($y_title, $tp['y_title_pos']);

$p->SetXTickIncrement(1);
$p->SetYTickIncrement(10);

if (isset($tp['xdatalabel'])) {
  $p->SetXTickLabelPos('none');
  $p->SetXDataLabelPos($tp['xdatalabel']);
} else {
  $p->SetXTickLabelPos($tp['xticklabel']);
}
if (isset($tp['xtick'])) $p->SetXTickPos($tp['xtick']);
else $p->SetXTickPos($tp['xticklabel']);

$p->SetYTickLabelPos($tp['yticklabel']);
if (isset($tp['ytick'])) $p->SetYTickPos($tp['ytick']);
else $p->SetYTickPos($tp['yticklabel']);

if (isset($tp['xticklen']))   $p->SetXTickLength($tp['xticklen']);
if (isset($tp['yticklen']))   $p->SetYTickLength($tp['yticklen']);
# If there are values of x<0, move the Y axis to X=0
if ($tp['xlt0']) {
  $p->SetYAxisPosition(0);
}
if (isset($tp['margins'])) {
  list($top, $right, $bottom, $left) = $tp['margins'];
  $p->SetMarginsPixels($left, $right, $top, $bottom);
}

$p->SetPlotType('linepoints');
$p->DrawGraph();
