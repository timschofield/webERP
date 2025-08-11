<?php

# PHPlot test: Horizontal plot Y axis default - Bars, all data < 0
require_once 'phplot.php';
$data = array(
   array('A', -2, -4),
   array('B', -7, -6),
   array('C', -5, -9),
   array('D', -3, -5),
);
$p = new Phplot\Phplot\phplot();
$p->SetTitle('Y Axis Position - horizontal bar plot, all negative data');
$p->SetDataType('text-data-yx');
$p->SetDataValues($data);
$p->SetPlotType('bars');
$p->SetXTickIncrement(1);
$p->SetYTickPos('none');
$p->DrawGraph();
