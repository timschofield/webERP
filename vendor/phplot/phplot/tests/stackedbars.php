<?php

# Test stacked bar plots, master script
# Originally from the PHPlot Reference Manual, Example: Stacked Bars...
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below.
# Notes: The 'compat' flag is used to make the resulting image compatible
# with the stackedbars1 and stackedbars2 tests before this was parameterized.
#   The ydatalabel parameter does nothing with PHPlot <= 5.1.0.
#   Setting xaxispos>0 breaks under PHPlot <= 5.1.0 (bug or unsupported).
#   'custom' param is a callback for tests needing more setup.

if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Stacked Bar Chart',  # First or only line
  'suffix' => '',           # Title part 2
  'compat' => False,        # True for backward-compatible image
  'shading' => NULL,        # Bar shading depth, 0 for off, NULL to skip
  'ydatalabel' => NULL,     # Y data label position, NULL to skip
  'xaxispos' => NULL,       # X axis position, default 0, NULL to skip.
  'edgedata' => FALSE,      # If true, use some low vals (0s and 1s) in the data
  'custom' => NULL,         # Custom callback function name.
        ), $tp);
require_once 'phplot.php';

$data = array(
  array('Jan', 40, 5, 10, 3), array('Feb', 90, 8, 15, 4),
  array('Mar', 50, 6, 10, 4), array('Apr', 40, 3, 20, 4),
  array('May', 75, 2, 10, 5), array('Jun', 45, 6, 15, 5),
  array('Jul', 40, 5, 20, 6), array('Aug', 35, 6, 12, 6),
  array('Sep', 50, 5, 10, 7), array('Oct', 45, 6, 15, 8),
  array('Nov', 35, 6, 20, 9), array('Dec', 40, 7, 12, 9),
);

# For some tests, we want some 0-height or very thin data rows.
if ($tp['edgedata']) {
  $data[1][2] = 0;
  $data[2][3] = 1;
  $data[5][4] = 0; // Last Y=0
  $data[7][3] = 1;
  $data[10][1] = $data[10][2] = 0;
}

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain');

$plot->SetPlotType('stackedbars');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);

if ($tp['compat']) {
  # backward compatible mode
  $plot->SetTitle('Candy Sales by Month and Product');
  $plot->SetYTitle('Millions of Units');
  $plot->SetLegend(array('Chocolates', 'Mints', 'Hard Candy', 'Sugar-Free'));
} else {
  $plot->SetTitle($tp['title'] . $tp['suffix']);
  $plot->SetXTitle('Month');
  $plot->SetYTitle('Number of Units');
}

$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');

if (isset($tp['shading'])) $plot->SetShading($tp['shading']);
if (isset($tp['xaxispos'])) $plot->SetXAxisPosition($tp['xaxispos']);
if (isset($tp['ydatalabel'])) $plot->SetYDataLabelPos($tp['ydatalabel']);
if (isset($tp['custom'])) call_user_func($tp['custom'], $plot);

$plot->DrawGraph();
