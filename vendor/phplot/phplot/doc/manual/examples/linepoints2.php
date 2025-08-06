<?php
# PHPlot Example: Linepoints plot with Data Value Labels
require_once __DIR__ . '/../../../src/phplot.php';

$data = array(
  array('1995', 135),
  array('1996', ''), // Missing data point
  array('1997', ''),
  array('1998', ''),
  array('1999', ''),
  array('2000', 225),
  array('2001', ''),
  array('2002', ''),
  array('2003', 456),
  array('2004', 420),
  array('2005', 373),
  array('2006', 300),
  array('2007', 255),
  array('2008', 283),
);

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain'); // Improves presentation in the manual
$plot->SetPlotType('linepoints');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetTitle("US Federal Emergency Food Assistance, 1995 - 2008\n"
              . "(in $ millions)");

# Turn on Y data labels:
$plot->SetYDataLabelPos('plotin');

# Turn on X data label lines (drawn from X axis up to data point):
$plot->SetDrawXDataLabelLines(True);

# With Y data labels, we don't need Y ticks, Y tick labels, or grid lines.
$plot->SetYTickLabelPos('none');
$plot->SetYTickPos('none');
$plot->SetDrawYGrid(False);
# X tick marks are meaningless with this data:
$plot->SetXTickPos('none');
$plot->SetXTickLabelPos('none');

$plot->DrawGraph();
