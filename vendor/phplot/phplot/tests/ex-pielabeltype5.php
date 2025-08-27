<?php

# PHPlot Example: Pie Chart Label Types - Multi-part labels
# This requires PHPlot >= 5.6.0
require_once 'phplot.php';
require_once 'ex-pielabeltypedata.php'; // Defines $data and $title

function mycallback($str)
{
    list($percent, $label) = explode(' ', $str, 2);
    return sprintf('%s (%.1f%%)', $label, $percent);
}

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain'); // Improves presentation in the manual
$plot->SetPlotType('pie');
$plot->SetDataType('text-data-single');
$plot->SetDataValues($data);
$plot->SetTitle($title);
# Set label type: combine 2 fields and pass to custom formatting function
$plot->SetPieLabelType(array('percent', 'label'), 'custom', 'mycallback');
$plot->DrawGraph();
