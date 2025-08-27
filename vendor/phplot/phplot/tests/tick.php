<?php

# PHPlot Test - Ticks, Lengths and Labels - baseline
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Tick Mark Variations',  # First or only line
  'suffix' => ' - Baseline',           # Title part 2
  'skiptick' => NULL,          # Skip ticks: NULL or string with BTRL
  'xticklen' => NULL,     # X Tick length (outside graph)
  'yticklen' => NULL,     # Y Tick length (outside graph)
  'xtickcross' => NULL,   # X Tick crossing (inside)
  'ytickcross' => NULL,   # Y Tick crossing (inside)
  'xdatalabel' => 'none', # X Data label position: none|both|plotup|plotdown
  'xticklabel' => 'plotdown', # X Tick & label position: none|both|plotup|plotdown
  'xtick' => NULL,        # X Tick override, if different from xticklabel
  'yticklabel' => 'plotleft', # Y Tick & label position: none|both|plotleft|plotright
        ), $tp);
require_once 'phplot.php';
# Notes: You can't have both X tick and data labels on, because if
# you turn one on PHPlot turns the other off.
# 'xtick' need only be set if you want it different from 'xticklabel', for
# example to have X tick marks and data labels.

$data = array(
  array('AAA', 0, 0),
  array('BBB', 1, 8),
  array('CCC', 2, 3),
  array('DDD', 3, 7),
  array('EEE', 4, 5),
);

$p = new Phplot\Phplot\phplot(400,300);
$p->SetTitle($tp['title'] . $tp['suffix']);
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetPlotAreaWorld(0, 0, 4, 10);
$p->SetXTickIncrement(1);
$p->SetYTickIncrement(1);
$p->SetPlotBorderType('full');
$p->SetXDataLabelPos($tp['xdatalabel']);
$p->SetXTickLabelPos($tp['xticklabel']);
if (isset($tp['xtick']))
  $p->SetXTickPos($tp['xtick']);
else
  $p->SetXTickPos($tp['xticklabel']);
$p->SetYTickLabelPos($tp['yticklabel']);
$p->SetYTickPos($tp['yticklabel']);
$p->SetXTitle('X TITLE');
$p->SetYTitle('Y TITLE');

# Tick skip tests:
if (isset($tp['skiptick'])) {
  $s = $tp['skiptick'];
  $n = strlen($s);
  for ($i = 0; $i < $n; $i++) {
    switch ($s[$i]) {
      case 'T': $p->SetSkipTopTick(True); break;
      case 'B': $p->SetSkipBottomTick(True); break;
      case 'L': $p->SetSkipLeftTick(True); break;
      case 'R': $p->SetSkipRightTick(True); break;
      default: die("Parameter error in tp['skiptick']");
    }
  }
}

# Tick size:
if (isset($tp['xtickcross'])) $p->SetXTickCrossing($tp['xtickcross']);
if (isset($tp['xticklen']))   $p->SetXTickLength($tp['xticklen']);
if (isset($tp['ytickcross'])) $p->SetYTickCrossing($tp['ytickcross']);
if (isset($tp['yticklen']))   $p->SetYTickLength($tp['yticklen']);

# Grids:
$p->SetDrawXGrid(False);
$p->SetDrawYGrid(False);

$p->SetPlotType('lines');
$p->DrawGraph();
