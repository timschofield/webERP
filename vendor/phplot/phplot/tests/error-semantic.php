<?php

# PHPlot error test - semantic error - mismatched data type and plot type
require_once 'phplot.php';
$data = array(array('a', 1, 1), array('b', 2, 3), array('c', 3, 5));
$plot = new Phplot\Phplot\phplot();
$plot->SetDataType('data-data');
$plot->SetDataValues($data);
$plot->SetPlotType('bars');
$plot->DrawGraph();
