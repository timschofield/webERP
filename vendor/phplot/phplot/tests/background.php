<?php

# PHPlot test: Background image tests
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Background Images',
  'suffix' => " (no background)",       # Title part 2
  'image1' => NULL, # Background image for entire graph, NULL for none
  'image2' => NULL, # Background image for plot area, NULL for none
  'mode1' => 'centeredtile',  # Graph background mode: centeredtile, tile, scale
  'mode2' => 'centeredtile',  # Plot area background mode: centeredtile, tile, scale
  'pabgnd' => False, # If image2 is null, draw a plot area background?
  'truecolor' => False,  # If true, use a Truecolor image
        ), $tp);
require_once 'phplot.php';
require_once 'phplot_truecolor.php';

# Use a bar graph because it is easier to see on the weird background.
$data = array(
  array('Jan', 1000), array('Feb', 2000), array('Mar', 3000),
  array('Apr', 2500), array('May', 1500), array('Jun',  500),
);
if ($tp['truecolor']) $plot = new Phplot\Phplot\phplot_truecolor(800, 600);
else $plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetPlotType('bars');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetTitle($tp['title'] . $tp['suffix']);
# Make a legend so we can see how the background behaves:
$plot->SetLegend(array('Widgets Produced'));
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
# Set Y range and tick interval:
$plot->SetPlotAreaWorld(NULL, 0, NULL, 4000);
$plot->SetYTickIncrement(500);
# Give it extra room around the plot area so we can see the backgrounds:
$plot->SetMarginsPixels(100, 100, 100, 100);

# Options:
if (isset($tp['image1']))
  $plot->SetBgImage($tp['image1'], $tp['mode1']);
if (isset($tp['image2']))
  $plot->SetPlotAreaBgImage($tp['image2'], $tp['mode2']);
elseif ($tp['pabgnd'])
  $plot->SetDrawPlotAreaBackground(True);

$plot->DrawGraph();
