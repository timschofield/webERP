<?php

# Testing phplot - "N" Lines with parameters
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Lines',
  'suffix' => ' (1 line, default styles)',  # Title part 2
  'nlines' =>  1,           # How many lines to draw (1-16)
  'LWidths' => NULL,        # SetLineWidths: integer or array or NULL
  'LStyles' => NULL,        # SetLineStyles: solid|dashed or array or NULL
  'DStyle' => NULL,         # SetDefaultDashedStyle: string or NULL
        ), $tp);

require_once 'phplot.php';

$np = $tp['nlines'];

$data = array();
for ($i = 1; $i <= 20; $i++) {
  $row = array('', $i);
  for ($j = 1; $j <= $np; $j++) $row[] = $i + $j;
  $data[] = $row;
}

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle($tp['title'] . $tp['suffix']);
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetPlotAreaWorld(0, 0, 21, 40);
$p->SetXTickIncrement(1);
$p->SetYTickIncrement(5);

# Options:
if (isset($tp['LWidths'])) $p->SetLineWidths($tp['LWidths']);
if (isset($tp['DStyle'])) $p->SetDefaultDashedStyle($tp['DStyle']);
if (isset($tp['LStyles'])) $p->SetLineStyles($tp['LStyles']);

$p->SetDrawXGrid(False);
$p->SetDrawYGrid(False);
$p->SetPlotType('lines');
$p->DrawGraph();
