<?php

# PHPlot test: Legend text & background color, baseline
# Based on discussion forum post 1/15/2013
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
# Merge and import test parameters:
extract(array_merge(array(
  'image_bg_color' => NULL,    # Image background color
  'plot_bg_color' => NULL,     # Plot area background color
  'legend_bg_color' => NULL,   # Legend background (requires SetLegendBgColor)
  'use_shapes' => False,       # True to use shape markers in legend
  'text_color' => NULL,        # Set general text color
  'legend_text_color' => NULL, # Set legend text color, overrides general

        ), $tp));
require_once 'phplot.php';

function ifset(&$var)
{
  return empty($var) ? '(not set)' : $var;
}

# Check for missing methods:
if (!empty($legend_bg_color) &&
      !method_exists('Phplot\Phplot\phplot', 'SetLegendBgColor')) {
    echo "Skipping test because it requires SetLegendBgColor()\n";
    exit(2); // Tells test suite to skip this test
}
if (!empty($legend_text_color) &&
      !method_exists('Phplot\Phplot\phplot', 'SetLegendTextColor')) {
    echo "Skipping test because it requires SetLegendTextColor()\n";
    exit(2); // Tells test suite to skip this test
}


$title = 'Legend Text & Background Color Control ('
       . ($use_shapes ? 'marker shapes' : 'color boxes') . ')'
       . "\n"
       . "\nImage background color: " . ifset($image_bg_color)
       . "\nPlot area background color: " . ifset($plot_bg_color)
       . "\nLegend background color: " . ifset($legend_bg_color)
       . "\nLegend text color: " . ifset($legend_text_color)
       . "\nGeneral text color: " . ifset($text_color);

$data = array(
  array('', 1, 1, 2, 3, 4, 5),
  array('', 2, 5, 1, 2, 3, 4),
  array('', 3, 4, 5, 1, 2, 3),
  array('', 4, 3, 4, 5, 1, 2),
  array('', 5, 2, 3, 4, 5, 1),
);

# Random legend lines, different lengths
$legend = array('Data set 1', 'Data set 2', 'Error', 'Difference', 'Alternate');

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle($title);
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetPlotType('points');
$p->SetLegend($legend);
$p->SetPlotAreaWorld(0, 0, 6, 6);
$p->SetPointSizes(8);

# Wide left margin, leaving room for legend:
$p->SetMarginsPixels(200);
# Put the legend to the left of plot area, starting about 1/4 down:
$p->SetLegendPosition(1, 0, 'plot', -0.1, 0.25);

# Colors:
if (!empty($image_bg_color)) $p->SetBackgroundColor($image_bg_color);
if (!empty($plot_bg_color)) {
    $p->SetPlotBgColor($plot_bg_color);
    $p->SetDrawPlotAreaBackground(True);
}
if (!empty($legend_bg_color)) $p->SetLegendBgColor($legend_bg_color);
$p->SetLegendUseShapes($use_shapes);
if (!empty($text_color)) $p->SetTextColor($text_color);
if (!empty($legend_text_color)) $p->SetLegendTextColor($legend_text_color);

$p->DrawGraph();
