<?php
# PHPlot Example: squared plot
require_once 'phplot.php';

# To get a repeatable test with 'random' data:
mt_srand(1);

# Make some noisy data:
$data = array();
for ($i = 0; $i < 100; $i++)
  $data[] = array('', $i / 4.0 + 2.0 + mt_rand(-20, 20) / 10.0);

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain');

$plot->SetPlotType('squared');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);

$plot->SetTitle('Noisy Data (squared plot)');

# Make the lines a bit wider:
$plot->SetLineWidths(2);

# Turn on the X grid (Y grid is on by default):
$plot->SetDrawXGrid(True);

# Use exactly this data range:
$plot->SetPlotAreaWorld(0, 0, 100, 40);

$plot->DrawGraph();
