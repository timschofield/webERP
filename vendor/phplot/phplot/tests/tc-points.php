<?php

# PHPlot test: Truecolor points plot, big overlapped dots
require_once 'phplot.php';
require_once 'phplot_truecolor.php';
mt_srand(0);
$data = array();
for ($i = 0; $i < 20; $i++) {
    $row = array('', $i);
    for ($j = 0; $j < 30; $j++) {
      $row[] = mt_rand(0, 100) / 10.0;
    }
    $data[] = $row;
}
$p = new Phplot\Phplot\phplot_truecolor(800, 600);
$p->SetTitle('Truecolor Points plot with varying size poionts');
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetDataColors(NULL, NULL, 90);
$p->SetLineWidths(3);
$p->SetLineStyles('solid');
$p->SetPointShapes('dot');
$p->SetPointSizes(array(10, 15, 20, 25, 30));
$p->SetPlotType('points');
$p->SetXTickIncrement(1);
$p->SetYTickIncrement(1);
$p->DrawGraph();
