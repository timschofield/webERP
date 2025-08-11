<?php
# PHPlot Example: Stacked Squared Area plot
require_once __DIR__ . '/../../../src/phplot.php';

$title = "US Oil Imports by Country, Top 5\n"
       . "Cumulative (stacked) Data";

$countries = array(
                'Canada', 'Saudi Arabia', 'Mexico', 'Venezuela', 'Nigeria',
#               --------  --------------  --------  -----------  ---------
);
$data = array(
  array('2002',     1445,           1519,     1500,        1201,      589),
  array('2003',     1549,           1726,     1569,        1183,      832),
  array('2004',     1616,           1495,     1598,        1297,     1078),
  array('2005',     1633,           1445,     1556,        1241,     1077),
  array('2006',     1802,           1423,     1577,        1142,     1037),
  array('2007',     1888,           1447,     1409,        1148,     1084),
  array('2008',     1956,           1503,     1187,        1039,      922),
);

# Append a duplicate of the last row, without label, to make it visible.
$n_rows = count($data);
$data[$n_rows] = $data[$n_rows-1];
$data[$n_rows][0] = '';

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetTitle($title);
$plot->SetYTitle('1000\'s of barrels per day');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetPlotType('stackedsquaredarea');
$plot->SetXTickPos('none');
$plot->SetLineStyles('solid');
$plot->SetYTickIncrement(250);
$plot->SetLegend($countries);
# Make room for the legend to the left of the plot:
$plot->SetMarginsPixels(200);
# Move the legend to the left:
$plot->SetLegendPixels(10, 10);
# Flip the legend order for stacked plots:
$plot->SetLegendReverse(True);
# To improve presentation in the manual:
$plot->SetImageBorderType('plain');
$plot->DrawGraph();
