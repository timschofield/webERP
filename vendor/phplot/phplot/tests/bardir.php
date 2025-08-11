<?php

# Bar chart bar direction test - baseline, range includes Y=0
# This tests for a bug though 5.1.0 that bars were drawn up from
# plot_min_y if y=0 was not in the graph and all Y<0.
#
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'suffix' => 'Range includes Y=0', # Title part 2
  'ymin' => -10,            # Min Y
  'ymax' => 10,             # Max Y
  'plottype' => 'bars',     # Plot type, bars or thinbarline
        ), $tp);
require_once 'phplot.php';

# Make data points inside the range.
$nd = 8;
$dy = ($tp['ymax'] - $tp['ymin']) / ($nd + 1);
$y = $tp['ymin'] + $dy;
$data = array();
for ($i = 1; $i <= $nd; $i++) {
  $data[] = array("P$i", $y);
  $y += $dy;
}

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle("Bar Chart Direction\nCase: " . $tp['suffix']);
$p->SetDataType('text-data');
$p->SetDataValues($data);

$p->SetXDataLabelPos('none');
$p->SetYLabelType('data', 2);
$p->SetXTickLabelPos('none');
$p->SetXTickPos('none');
$p->SetYDataLabelPos('plotin');
$p->SetPlotAreaWorld(NULL, $tp['ymin'], NULL, $tp['ymax']);
$p->SetPlotType($tp['plottype']);
$p->DrawGraph();
