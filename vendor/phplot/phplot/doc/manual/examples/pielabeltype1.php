<?php
# PHPlot Example: Pie Chart Label Types - baseline, default label type
# This requires PHPlot >= 5.6.0
require_once __DIR__ . '/../../../src/phplot.php';
require_once 'pielabeltypedata.php'; // Defines $data and $title

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain'); // Improves presentation in the manual
$plot->SetPlotType('pie');
$plot->SetDataType('text-data-single');
$plot->SetDataValues($data);
$plot->SetTitle($title);
$plot->DrawGraph();
