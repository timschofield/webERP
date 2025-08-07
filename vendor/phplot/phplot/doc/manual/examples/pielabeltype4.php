<?php
# PHPlot Example: Pie Chart Label Types - Index and custom callback
# This requires PHPlot >= 5.6.0
require_once __DIR__ . '/../../../src/phplot.php';
require_once 'pielabeltypedata.php'; // Defines $data and $title

$mylabels = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
function mycallback($index)
{
    global $mylabels;
    return $mylabels[$index];
}

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain'); // Improves presentation in the manual
$plot->SetPlotType('pie');
$plot->SetDataType('text-data-single');
$plot->SetDataValues($data);
$plot->SetTitle($title);
# Set label type: Pass segment index (0-N) to custom formating function
$plot->SetPieLabelType('index', 'custom', 'mycallback');
$plot->DrawGraph();
