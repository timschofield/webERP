<?php

# Testing phplot - Transparent, GIF output (it works with PNG too)
# This really needs to be displayed in a browser above something to see.
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Transparent Background',
  'suffix' => " (default)",           # Title part 2
  'FFormat' => NULL,         # FileFormat: gif, png, or jpg (won't work)
        ), $tp);

require_once 'phplot.php';

$data = array(
  array('', -4, -64,  16,  40),
  array('', -3, -27,   9,  30),
  array('', -2,  -8,   4,  20),
  array('', -1,  -1,   1,  10),
  array('',  0,   0,   0,   0),
  array('',  1,   1,   1, -10),
  array('',  2,   8,   4, -20),
  array('',  3,  27,   9, -30),
  array('',  4,  64,  16, -40),
);

$p = new Phplot\Phplot\phplot();

# File format GIF or PNG, to test transparency:
if (isset($tp['FFormat'])) $p->SetFileFormat($tp['FFormat']);

# Background color will be transparent:
$p->SetBackgroundColor('yellow');
$p->SetTransparentColor('yellow');

$p->SetTitle($tp['title'] . $tp['suffix']);
$p->SetDataType('data-data');
$p->SetDataValues($data);

# We don't use the data labels (all set to '') so might as well turn them off:
$p->SetXDataLabelPos('none');

# Need to set area and ticks to get reasonable choices.
$p->SetPlotAreaWorld(-4, -70, 4, 80);
$p->SetXTickIncrement(1);
$p->SetYTickIncrement(10);

# Don't use dashes for 3rd line:
$p->SetLineStyles('solid');

# Make the lines thicker:
$p->SetLineWidths(3);

# Draw both grids:
$p->SetDrawXGrid(True);
$p->SetDrawYGrid(True);

# And a legend:
$p->SetLegend(array('x^3', 'x^2', '-10x'));

$p->SetPlotType('lines');
$p->DrawGraph();
