<?php
# PHPlot Example: Pie Chart Label Types - Labels from data array
# This requires PHPlot >= 5.6.0
require_once 'phplot.php';
require_once 'ex-pielabeltypedata.php'; // Defines $data and $title

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain'); // Improves presentation in the manual
$plot->SetPlotType('pie');
$plot->SetDataType('text-data-single');
$plot->SetDataValues($data);
$plot->SetTitle($title);
# Set label type: Use labels from data array
$plot->SetPieLabelType('label');
$plot->DrawGraph();
