<?php

# PHPlot test: SetPrecisionY() vs pie chart
# Reference Manual says SetPrecisionY is overriden by SetPieLabelType
# Set $override = TRUE and include this script to test the override.
require_once 'phplot.php';
if (empty($override)) $override = FALSE;

$data = array(
  array('', 1),
  array('', 2),
  array('', 1),
  array('', 3),
);

$p = new Phplot\Phplot\phplot(800, 800);

$title = "Pie Chart Label Precision\n";
if ($override) {
    $title .= 'Overide SetPrecisionY(2) with SetPieLabelType(..., 5)';
} else {
    $title .= 'Use SetPrecisionY(2) with no SetPieLabelType()';
}
$p->SetTitle($title);
$p->SetDataType('text-data-single');
$p->SetDataValues($data);
$p->SetPlotType('pie');
$p->SetPrecisionY(2);
if ($override) $p->SetPieLabelType('percent', 'data', 5, '', '%');
$p->DrawGraph();
