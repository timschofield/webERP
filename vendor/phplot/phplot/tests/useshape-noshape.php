<?php

# PHPlot test: 'none' as point shape vs legend with LegendUseShapes(True)
# This is bug #166: A linepoints plot with legend, legend using point
# shape markers (LegendUseShapes(True)), and one or more data sets is set
# to use 'none' as the point marker (to get a line only, no points).
# The bug is that there would be no color marker at all in the legend for
# those data sets.
require_once 'phplot.php';

# To test with other plot types, scripts can set $plot_type and then
# include this script.
if (empty($plot_type)) $plot_type = 'linepoints';

$data = array();
$nx = 20;
$ny = 5;
for ($x = 0; $x < $nx; $x++) {
  $row = array('', $x);
  for ($iy = 0; $iy < $ny; $iy++) $row[] = $ny - $iy;
  $data[] = $row;
}
$shapes = array('dot', 'bowtie', 'none', 'rect', 'none');
for ($iy = 0; $iy < 5; $iy++)
    $legend[$iy] = "Data Set #$iy using shape {$shapes[$iy]}";

$p = new Phplot\Phplot\phplot(800, 600);

$p->SetTitle("$plot_type plot with legend using point shapes, and\n"
           . "shape='none' on 2 of the data sets");
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetPlotType($plot_type);
$p->SetLineStyles('solid');
$p->SetDrawYGrid(False);
$p->SetPointShapes($shapes);
$p->SetLegend($legend);
$p->SetLegendUseShapes(True);
$p->DrawGraph();
