<?php

# Stacked bar plots with data labels, raised X axis covering some points
require_once 'phplot.php';
$data = array(
    array('A', 2, 2, 2),
    array('B', 3, 3, 3),
    array('C', 4, 4, 4),
    array('D', 5, 5, 5),
    array('E', 6, 6, 6),
    array('F', 7, 7, 7),
    array('G', 8, 8, 8),
);
$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetPlotType('stackedbars');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetTitle('Stacked Bars - Raised axis covers segments');
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->SetXAxisPosition(7);
$plot->SetYTickIncrement(1);
$plot->SetYDataLabelPos('plotstack');
$plot->DrawGraph();
