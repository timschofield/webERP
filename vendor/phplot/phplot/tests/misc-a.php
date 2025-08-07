<?php

# Miscellaneous options: color map, line spacing, grid line style
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => "Miscellaneous Options\nColor Map, Line Spacing, Dashed Grid",
  'suffix' => " (baseline)",       # Title part 2
  'colormap' => NULL,    # Custom color map, NULL for none.
  'datacolors' => NULL,  # Data Colors and Error Bar Colors maps, NULL for none.
  'linespacing' => NULL, # SetLineSpacing, or NULL to default
  'dashedgrid' => True,  # SetDrawDashedGrid, False for solid, True for dashed
        ), $tp);
require_once 'phplot.php';

#                          Land area in 10^6 sq km
#
$data = array(
  array('Monday',      10, 23,  7, 15),
  array('Tuesday',     25,  7, 12,  9),
  array('Wednesday',    8, 15, 18, 15),
  array('Thursday',    16,  9, 26, 16),
  array('Friday',      20, 25, 21, 14),
);

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetPlotType('bars');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);

# Options:
if (isset($tp['linespacing']))
   $plot->SetLineSpacing($tp['linespacing']);
if (isset($tp['colormap']))
   $plot->SetRGBArray($tp['colormap']);
if (isset($tp['datacolors'])) {
   $plot->SetDataColors($tp['datacolors']);
   $plot->SetErrorBarColors($tp['datacolors']);
}
$plot->SetDrawDashedGrid($tp['dashedgrid']);

$plot->SetTitle($tp['title'] . "\n" . $tp['suffix']);
$plot->SetLegend(array('Data Set 1', 'Data Set 2', 'Data Set 3', 'Data Set 4'));
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->SetDrawXGrid(True);
$plot->SetDrawYGrid(True);
$plot->SetPlotAreaWorld(NULL, 0, NULL, 30);
$plot->SetNumYTicks(30);

$plot->DrawGraph();
