<?php

# PHPlot test - Pie Chart minimum size vs autosize
# The idea is that setting pie_min_size_factor=1 should be the
# same as turning off autosize.
# Set $use_autosize=TRUE and call this script for the other case.
require_once 'phplot.php';

$title = 'Pie Chart Autosize vs min_size_factor';
if (empty($use_autosize)) {
    $title .= "\nUsing pie_min_size_factor=1";
} else {
    $title .= "\nUsing SetPieAutoSize(False)";
}

$data = array(
    array('==LABEL==', 1),
    array('==LABEL==', 2),
    array('==LABEL==', 1),
);

$plot = new Phplot\Phplot\phplot(600, 400);
$plot->SetPlotType('pie');
$plot->SetDataType('text-data-single');
$plot->SetDataValues($data);
$plot->SetFontGD('generic', 5);
$plot->SetTitle($title);
$plot->SetPieLabelType('label');
$plot->SetPlotBorderType('full');
if (empty($use_autosize)) {
    $plot->pie_min_size_factor = 1.0;
} else {
    $plot->SetPieAutoSize(False);
}
$plot->DrawGraph();
