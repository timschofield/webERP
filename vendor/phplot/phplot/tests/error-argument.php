<?php

# PHPlot error test - Bad argument value to a function.
require_once 'phplot.php';
$data = array(array('a', 1, 1), array('b', 2, 3), array('c', 3, 5));
$plot = new Phplot\Phplot\phplot();
$plot->SetDataType('data-text'); # Error!
$plot->SetDataValues($data);
$plot->SetPlotType('lines');
$plot->DrawGraph();
