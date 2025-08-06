<?php

# PHPlot test: Horizontal plot Y axis default - thinbarline, all data < 0
require_once 'phplot.php';
$data = array(
   array('A', -2),
   array('B', -7),
   array('C', -5),
   array('D', -3),
);
$p = new Phplot\Phplot\phplot();
$p->SetTitle('Y Axis Position - horizontal thinbarline, all negative data');
$p->SetDataType('text-data-yx');
$p->SetDataValues($data);
$p->SetPlotType('thinbarline');
$p->SetXTickIncrement(1);
$p->SetYTickPos('none');
$p->DrawGraph();
