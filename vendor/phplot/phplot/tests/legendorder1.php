<?php
# PHPlot test: Legend order reversal, master / stackedbars baseline
#  Parameters can be set by a calling test:
#    $reverse = True or False
#    $plot_type = some plot type that accepts text-data data arrays.
require_once 'phplot.php';

if (empty($reverse)) {
  $dir = 'Default, top-to-bottom';
} else {
  $dir = 'Reversed, bottom-to-top';
}
if (empty($plot_type)) $plot_type = 'stackedbars';

$data = array(
  array('Jan', 40, 5, 10, 3), array('Feb', 90, 8, 15, 4),
  array('Mar', 50, 6, 10, 4), array('Apr', 40, 3, 20, 4),
  array('May', 75, 2, 10, 5), array('Jun', 45, 6, 15, 5),
  array('Jul', 40, 5, 20, 6), array('Aug', 35, 6, 12, 6),
  array('Sep', 50, 5, 10, 7), array('Oct', 45, 6, 15, 8),
  array('Nov', 35, 6, 20, 9), array('Dec', 40, 7, 12, 9),
);
$legend = array(
  'Dataset 1: 40,...',
  'Dataset 2: 5,...',
  'Dataset 3: 10,...',
  'Dataset 4: 3,...',
);

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetPlotType($plot_type);
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetTitle("Legend Order - $plot_type - $dir");
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->SetLegend($legend);
if (isset($reverse)) $plot->SetLegendReverse($reverse);
$plot->DrawGraph();
