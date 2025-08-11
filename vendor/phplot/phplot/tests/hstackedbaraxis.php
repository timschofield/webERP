<?php

# Horizontal Stacked Bar with moved Y axis
require_once 'phplot.php';
$data = array(
    array('A', 1, 1, 1),
    array('B', 2, 2, 2),
    array('C', 3, 3, 3),
    array('D', 4, 4, 4),
    array('E', 5, 5, 5),
    array('F', 6, 6, 6),
    array('G', 7, 7, 7),
    array('H', 8, 8, 8),
);
$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetPlotType('stackedbars');
$plot->SetDataType('text-data-yx');
$plot->SetDataValues($data);
$plot->SetTitle('Horizontal Stacked Bars with Y axis moved');
$plot->SetYTickPos('none');
$plot->SetYAxisPosition(7);
$plot->SetPlotAreaWorld(7, NULL, NULL, NULL);
$plot->SetXTickIncrement(1);
$plot->SetXDataLabelPos('plotstack');
$plot->SetXTitle('X AXIS');
$plot->SetYTitle('Y AXIS');
$plot->DrawGraph();
