<?php

# PHPlot test: Reset transparent color (fixed at 6.0.0), case 0
# Set $case to 1 and include this script to reset the transparent color.
require_once 'phplot.php';

$data = array(
  array('Monday', 10),
  array('Tuesday', 20),
  array('Wednesday', 30),
  array('Thursday', 20),
  array('Friday', 50),
  array('Saturday', 5),
  array('Sunday', 10),
);

$p = new Phplot\Phplot\phplot();
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType('bars');

$p->SetFileFormat('gif');
$p->SetBackgroundColor('yellow');
$p->SetTransparentColor('yellow');

$title = "Transparent Color Reset Test";
if (empty($case)) {
    $title .= "\nBaseline - Transparent background";
} else {
    $title .= "\nReset - No transparent background";
    $p->SetTransparentColor(NULL);
}

$p->SetTitle($title);
$p->DrawGraph();
