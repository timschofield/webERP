<?php

# PHPlot test: Truecolor area plot, alpha gradient
require_once 'phplot.php';
require_once 'phplot_truecolor.php';

$data = array(
  array('',  90, 60, 45, 40, 20,  0),
  array('', 100, 80, 55, 40, 22, 15),
  array('',  83, 65, 47, 47, 24, 20),
);
$colors = array(
 'red:100', 'red:80', '#ff00003c', 'red:40', 'red:20', 'red',
);
$p = new Phplot\Phplot\phplot_truecolor(800, 800);
$p->SetTitle("Truecolor Area chart with red alpha gradient");
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetDataColors($colors);
$p->SetPlotType('area');
$p->SetDrawXGrid(True);
$p->SetXTickLabelPos('none');
$p->SetXTickPos('none');
$p->DrawGraph();
