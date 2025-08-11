<?php
# PHPlot Example: thinbarline plot
require_once __DIR__ . '/../../../src/phplot.php';

# To get repeatable results with 'random' data:
mt_srand(1);

# Make some noisy data:
$data = array();
for ($i = 0; $i < 100; $i++)
  $data[] = array('', $i / 4.0 + 2.0 + mt_rand(-20, 20) / 10.0);

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain');

$plot->SetPlotType('thinbarline');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);

# Main plot title:
$plot->SetTitle('Noisy Data (thinbarline)');

$plot->DrawGraph();
