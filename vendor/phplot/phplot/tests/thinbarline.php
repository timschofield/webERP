<?php

# PHPlot Example: thinbarline plot
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Noisy random data (thinbarline)',
  'suffix' => "",           # Title part 2
  'LWidth' => NULL,         # LineWidth: integer or NULL to omit
        ), $tp);

require_once 'phplot.php';

# To get a repeatable test with 'random' data:
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
$plot->SetTitle($tp['title'] . $tp['suffix']);

# Options:
if (isset($tp['LWidth'])) $plot->SetLineWidths($tp['LWidth']);

$plot->DrawGraph();
