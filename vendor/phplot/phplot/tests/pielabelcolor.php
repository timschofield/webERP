<?php

# PHPlot test: Pie label color - baseline
# Other scripts can set $case and include this script:
#   case 0) Baseline - no colors set. Labels will be black.
#   case 1) GridColor set, PieLabelColor not set. Labels use GridColor.
#   case 2) GridColor set, PieLabelColor set. Labels use PieLabelColor.
# For post-5.6.0 with new color control function.
require_once 'phplot.php';

if (empty($case)) $case = 0;
$colors = array(
  'grid' => 'green',
  'pielabel' => 'blue',
);

$data = array(
  array('Baseball',    100),
  array('Football',     85),
  array('Basketball',   53),
  array('Hockey',       48),
);
$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetDataType('text-data-single');
$plot->SetDataValues($data);
$plot->SetPlotType('pie');

$title = "Pie Chart Label Color\n";

if ($case == 1 || $case == 2) {
    $plot->SetGridColor($colors['grid']);
    $title .= "GridColor set to {$colors['grid']}\n";
} else {
    $title .= "GridColor is NOT set\n";
}
if ($case == 2) {
    $plot->SetPieLabelColor($colors['pielabel']);
    $title .= "PieLabelColor set to {$colors['pielabel']}\n";
} else {
    $title .= "PieLabelColor is NOT set\n";
}
$plot->SetPieLabelType(array('label', 'value'));
foreach ($data as $row) $plot->SetLegend($row[0]);
$plot->SetTitle($title);
$plot->DrawGraph();
