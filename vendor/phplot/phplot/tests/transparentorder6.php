<?php

# PHPlot test - transparency - palette, set data color then set transparent
require_once 'phplot.php';
$data = array(array('A', 6), array('B', 4), array('C', 2), array('D', 0));
$p = new Phplot\Phplot\phplot();
$p->SetTitle('Palette, Set data color, Set transparent');
$p->SetDataValues($data);
$p->SetPlotType('bars');
$p->SetDataColors('yellow');
$p->SetTransparentColor('yellow');
$p->DrawGraph();
