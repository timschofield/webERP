<?php

# Testing PHPlot: Bar charts
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Bar Chart with 3 Data Sets',
  'suffix' => "",           # Title part 2
  'IBType' => NULL,         # ImageBorderType: plain or raised or NULL
  'Shade' => NULL,          # Shading: 0 for none or pixels or NULL to omit
  'DBColors' => NULL,       # DataBorderColors: color or array or NULL
        ), $tp);
require_once 'phplot.php';

$data = array(
  array('Jan', 40, 2, 4), array('Feb', 30, 3, 4), array('Mar', 20, 4, 4),
  array('Apr', 10, 5, 4), array('May',  3, 6, 4), array('Jun',  7, 7, 4),
  array('Jul', 10, 8, 4), array('Aug', 15, 9, 4), array('Sep', 20, 5, 4),
  array('Oct', 18, 4, 4), array('Nov', 16, 7, 4), array('Dec', 14, 3, 4),
);

$plot = new Phplot\Phplot\phplot(800, 600);

$plot->SetPlotType('bars');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);

# Main plot title:
$plot->SetTitle($tp['title'] . $tp['suffix']);

# Make a legend for the 3 data sets plotted:
$plot->SetLegend(array('Engineering', 'Manufacturing', 'Administration'));

# Turn off X tick labels and ticks because they don't apply here:
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');

# Options:
if (isset($tp['IBType'])) $plot->SetImageBorderType($tp['IBType']);
if (isset($tp['Shade'])) $plot->SetShading($tp['Shade']);
if (isset($tp['DBColors'])) $plot->SetDataBorderColors($tp['DBColors']);

$plot->DrawGraph();
