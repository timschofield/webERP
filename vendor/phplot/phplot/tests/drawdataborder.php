<?php

# PHPlot test - SetDrawDrawBorders (PHPlot >= 6.0) - master
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'plottype' => 'bars',     # Plot type: bars or stackedbars
  'shading' => NULL,        # Shaded: NULL for default shading, 0 unshaded, etc.
  'draw_borders' => NULL,   # SetDrawDataBorders: NULL to not call, True, False
        ), $tp);
require_once 'phplot.php';

# Check for PHPlot method and skip the test if it is missing:
if (!method_exists('Phplot\Phplot\phplot', 'SetDrawDataBorders')) {
    echo "Skipping test because it requires SetDrawDataBorders()\n";
    exit(2);
}

$data = array(
    array('A', 10, 1, 10),
    array('B', 20, 2, 10),
    array('C', 30, 0, 10),
    array('D', 0, 0, 0),
    array('E', -10, -5, -3),
    array('F', -20, -15, 0),
);

# Build a title:
if (isset($tp['shading']) && $tp['shading'] == 0)
    $title = 'Unshaded ';
else
    $title = 'Shaded ';
$title .= $tp['plottype'] . " Plot with Data Borders Control:\n"
       .  'Draw Data Borders: ';
if (!isset($tp['draw_borders'])) $title .= '(default)';
elseif ($tp['draw_borders']) $title .= 'Enabled';
else $title .= 'Disabled';


$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle($title);
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType($tp['plottype']);
if (isset($tp['shading'])) $p->SetShading($tp['shading']);
if (isset($tp['draw_borders'])) $p->SetDrawDataBorders($tp['draw_borders']);
$p->DrawGraph();
