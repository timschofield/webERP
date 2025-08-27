<?php

# PHPlot test: Bug #3296884 Undefined variable with stackedbars (up, 0s, down)
$data = array( array('A', 1, 2), array('B', 0, 0), array('C', -1, -2));
require_once 'phplot.php';
$plot = new Phplot\Phplot\phplot(400, 300);
$plot->SetTitle('Stacked bar 0 bug: up, 0, down');
$plot->SetPlotType('stackedbars');
$plot->SetDataValues($data);
$plot->SetYDataLabelPos('plotstack');
$plot->DrawGraph();
