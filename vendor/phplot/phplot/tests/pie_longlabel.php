<?php

# Testing phplot - Fix for SetPlotAreaPixels with pie charts.
require_once 'phplot.php';

$data = array();
$legend = array();
for ($i = 0; $i < 15; $i++) {
  $data[] = array('', 10+$i);
  $legend[] = "A very long label for set $i";
}

$p = new Phplot\Phplot\phplot(800,500);

$p->SetPlotAreaPixels(240, 20, 795, 495);
$p->SetLegendPixels(5, 40);
$p->SetLegend($legend);

$p->SetTitle("Pie chart, SetPlotAreaPixels, Long Labels");
$p->SetDataType('text-data-single');
$p->SetDataValues($data);
$p->SetPlotType('pie');

# Color background so we can see what's what:
$p->SetDrawPlotAreaBackground(True);
$p->SetPlotBgColor('gray');

$p->DrawGraph();
