<?php

# Missing Y values in data-data-error plots - main
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Error Plot, missing Y values',  # First or only line
  'suffix' => "\nlines, single, no draw-broken",   # Title part 2
  'multiline' => False,     # True for 3 lines, false for 1 line.
  'plot-type' => 'lines',   # lines, points, linepoints
  'draw-broken' => False,   # See SetDrawBrokenLines
        ), $tp);
require_once 'phplot.php';

# data-data-error rows: label, X, Y1, +err1, -err1, Y2, +err2, -err2 ...
if ($tp['multiline']) {
  $data = array(
    array('', 1,   10, 5, 5,  95, 5, 5,   40, 3, 3),
    array('', 2,   20, 5, 5,  75, 5, 5,   '', 3, 3), # one missing Y
    array('', 3,   30, 5, 5,  85, 5, 5,   48, 3, 3),
    array('', 4,   '', 5, 5,  '', 5, 5,   '', 3, 3), # All missing Y
    array('', 5,   50, 5, 5,  55, 5, 5,   54, 3, 3),
    array('', 6,   60, 5, 5,  65, 5, 5,   56, 3, 3),
    array('', 7,   70, 5, 5,  35, 5, 5,   58, 3, 3),
  );
} else {
  $data = array(
    array('', 1,   10, 5, 5),
    array('', 2,   40, 5, 5),
    array('', 3,   30, 5, 5),
    array('', 4,   '', 5, 5),  # Missing Y
    array('', 5,   50, 5, 5),
    array('', 6,   20, 5, 5),
    array('', 7,   60, 5, 5),
  );
}

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle($tp['title'] . $tp['suffix']);
$p->SetDataType('data-data-error');
$p->SetDataValues($data);
# Set X range only, let it figure out Y.
$p->SetPlotAreaWorld(0, NULL, 8, NULL);
$p->SetXTickIncrement(1);
$p->SetYTickIncrement(5);
$p->SetPlotType($tp['plot-type']);
$p->SetDrawXGrid(False);
$p->SetDrawYGrid(False);

$p->SetDrawBrokenLines($tp['draw-broken']);

$p->DrawGraph();
