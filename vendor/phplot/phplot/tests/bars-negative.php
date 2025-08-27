<?php

# Testing phplot - Bars, negative values, as strings
require_once 'phplot.php';

$data = array(array('A', '10', '-5'), array('B', '-10', '5'));
$p = new Phplot\Phplot\phplot();
$p->SetTitle('Bars with negative string values');
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetXTickLabelPos('none');
$p->SetXTickPos('none');
$p->SetPlotType('bars');
#$p->SetDataBorderColors('red');
#$p->SetShading(0);
#$p->SetXAxisPosition(0);
$p->DrawGraph();
