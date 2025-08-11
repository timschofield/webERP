<?php

# PHPlot error test - cannot use coordinate translation before DrawGraph
require_once 'phplot.php';
$data = array( array('A', 1), array('B', 2));
$p = new Phplot\Phplot\phplot();
$p->SetDataType('text-data');
$p->SetPlotType('bars');
$p->SetDataValues($data);
# Error check: Can't call this yet:
list($x,$y) = $p->GetDeviceXY(0, 0);
$p->DrawGraph();
