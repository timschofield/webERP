<?php

# PHPlot test - transparency - palette, set transparent then set background
# Note: This group of tests was created for bug #3045131
require_once 'phplot.php';
$data = array(array('A', 6), array('B', 4), array('C', 2), array('D', 0));
$p = new Phplot\Phplot\phplot();
$p->SetTitle('Palette, Set transparent, Set background color');
$p->SetDataValues($data);
$p->SetPlotType('bars');
$p->SetTitleColor('green'); // For contrast vs black/clear background
$p->SetTransparentColor('yellow');
$p->SetBackgroundColor('yellow');
$p->DrawGraph();
