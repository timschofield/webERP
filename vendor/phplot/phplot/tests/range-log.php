<?php

# PHPlot test: Log/log scales
# Note: This checks for a bug in PHPlot-5.8.0 and earlier where it
# only checked Y, not X for 0 when log scale was on, and so it missed
# the case of xmin=0.
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'c' => 10,         # Plotting XY = C
  't' => 1,          # If set, use this for X and Y tick steps, else auto
  'ar' => FALSE,     # Auto-range: if false, use SetPlotAreaWorld
        ), $tp);
require_once 'phplot.php';

# Extract all test parameters as local variables:
extract($tp);

$title = "Log/Log Axis Test\nPlotting: XY = $c\n"
        . "Tick step: " . (empty($t) ? "Auto" : $t) . ", "
        . "Range: " . ($ar ?  "Auto" : "Manually set");

# Plot X*Y=C
$data = array();
for ($x = 1; $x <= $c; $x++) $data[] = array('', $x, $c/$x);

$p = new Phplot\Phplot\phplot(800, 600);

$p->SetDataType('data-data');
$p->SetDataValues($data);

$p->SetTitle($title);
$p->SetXScaleType('log');
$p->SetYScaleType('log');
if (empty($t)) {
    $p->SetXTickIncrement($t);
    $p->SetYTickIncrement($t);
}
if (!$ar) $p->SetPlotAreaWorld(0, 1, $c+1, $c+1);
$p->SetXTickAnchor(0);
$p->SetYTickAnchor(0);
$p->SetDrawXGrid(True);
$p->SetDrawYGrid(True);
$p->SetPlotType('lines');
$p->DrawGraph();
