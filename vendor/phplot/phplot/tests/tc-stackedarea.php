<?php

# PHPlot test: Truecolor stacked area plot
require_once 'phplot.php';
require_once 'phplot_truecolor.php';

$data = array(
  array('A',  2, 3, 1, 3, 2, 1),
  array('B',  4, 1, 5, 0, 4, 2),
  array('C',  2, 3, 1, 3, 2, 3),
  array('D',  4, 1, 5, 0, 4, 4),
);
$p = new Phplot\Phplot\phplot_truecolor(800, 800);
$p->SetTitle("Truecolor Stacked Area chart with alpha channel");
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetDataColors(NULL, NULL, 60); // Set alpha for all
$p->SetPlotType('stackedarea');
$p->SetDrawXGrid(True);
$p->SetXTickLabelPos('none');
$p->SetXTickPos('none');
$p->DrawGraph();
