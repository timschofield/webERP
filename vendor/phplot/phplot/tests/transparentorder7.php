<?php

# PHPlot test - transparency - truecolor, set transparent then set data color
require_once 'phplot.php';
require_once 'phplot_truecolor.php';
$data = array(array('A', 6), array('B', 4), array('C', 2), array('D', 0));
$p = new Phplot\Phplot\phplot_truecolor;
$p->SetTitle('Truecolor, Set transparent, Set data color');
$p->SetDataValues($data);
$p->SetPlotType('bars');
$p->SetTransparentColor('yellow');
$p->SetDataColors('yellow');
$p->DrawGraph();
