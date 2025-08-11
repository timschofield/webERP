<?php

# PHPlot test: Legend Colorbox Borders (PHPlot >= 6.0.0) - master
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'colorboxborders' => NULL,  # NULL or none textcolor databordercolor
  'colorboxwidth' => NULL,  # NULL, 1 for default width, etc
  'setdbcolors' => FALSE,   # True to change Data Border colors
  'settextcolor' => FALSE,  # True to change Text color
        ), $tp);
require_once 'phplot.php';

# Check for new phplot method and skip the test if it is missing:
if (!method_exists('Phplot\Phplot\phplot', 'SetLegendColorboxBorders')) {
    echo "Skipping test because it requires SetLegendColorboxBorders()\n";
    exit(2);
}

# Title building helper for string variables (which might be NULL):
function cond($name, &$var)
{
    return $name . ": " . (isset($var) ? $var : '(default)') . "\n";
}

# Title building helper for bool variables:
function bcond($name, $var)
{
    return $name . ": " . ($var ? "True" : "False") . "\n";
}


$data = array(
        array('A', 10, 20, 30, 40, 50, 60, 70, 80, 90, 100),
        array('B', 10, 20, 30, 40, 50, 60, 70, 80, 90, 100),
        array('C', 10, 20, 30, 40, 50, 60, 70, 80, 90, 100),
    );

$n_cols = count($data[0]) - 1;
for ($i = 0; $i < $n_cols; $i++) $legend[$i] = "== Row $i ==";

$title = "Legend Colorbox Borders Control\n"
       . cond("Color box borders: ", $tp['colorboxborders'])
       . cond("Color box width factor: ", $tp['colorboxwidth'])
       . bcond("Set Data Border Colors", $tp['setdbcolors'])
       . bcond("Set Text Color", $tp['settextcolor']);

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle($title);
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType('bars');
$p->SetShading(0); // Unshaded so borders can be seen

$p->SetLegend($legend);
$p->SetLegendPosition(0, 0, 'plot', 0, 0, 5, 5);
if (isset($tp['colorboxborders']))
    $p->SetLegendColorboxBorders($tp['colorboxborders']);
if (isset($tp['colorboxwidth']))
    $p->legend_colorbox_width = $tp['colorboxwidth'];

if ($tp['setdbcolors'])
    $p->SetDataBorderColors(array('red', 'green', 'blue'));

if ($tp['settextcolor'])
    $p->SetTextColor('blue');

$p->DrawGraph();
