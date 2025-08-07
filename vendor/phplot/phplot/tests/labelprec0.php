<?php

# PHPlot test: SetPrecisionX() and SetPrecisionY() versus label type
# Set $horizontal = TRUE and include this script for horizontal plot.
# Reference manual says these apply to both tick and data labels.
require_once 'phplot.php';
if (empty($horizontal)) $horizontal = FALSE;

$data = array(
  array(99.1414, 2.3,  3.3),
  array(55.6543, 4.6, 12.345),
);

$p = new Phplot\Phplot\phplot(800, 800);

$title = "SetPrecisionX(2); SetPrecisionY(3);\n";
if ($horizontal) {
    $title .= 'Horizontal Plot: X data labels within, Y data labels on right';
} else {
    $title .= 'Vertical Plot: Y data labels within, X data labels above';
}
$p->SetTitle($title);

$p->SetDataType($horizontal ? 'data-data-yx' : 'data-data');

$p->SetDataValues($data);
$p->SetPlotType('lines');

$p->SetPrecisionX(2);
$p->SetPrecisionY(4);

$p->SetXTitle("X Axis");
$p->SetYTitle("Y Axis");

$p->SetXTickLabelPos('plotdown');
$p->SetYTickLabelPos('plotleft');
if ($horizontal) {
    $p->SetXDataLabelPos('plotin');
    $p->SetYDataLabelPos('plotright');
} else {
    $p->SetXDataLabelPos('plotup');
    $p->SetYDataLabelPos('plotin');
}

$p->DrawGraph();
