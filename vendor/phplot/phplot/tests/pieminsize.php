<?php

# PHPlot test - Pie Chart minimum size cut-off - baseline
# This is a parameterized test. Other scripts can set the parameters below
# and then include this script.
#   $label_size  : Number of characters in a pie label
#   $suffix : Line 2 of plot title
#   $min_size_factor : If set, this sets pie_min_size_factor, which determines
#      the minimum percentage of the plot area that the actual pie will use.
require_once 'phplot.php';

# Set defaults:
if (!isset($suffix)) $suffix = 'Default min_size, Labels Fit';
if (!isset($label_size)) $label_size = 10;

# Built plot title:
$title = "Pie Chart Min Size Test: $label_size char label";
if (isset($min_size_factor))
  $title .= sprintf(', min size=%d%%', (int)(100*$min_size_factor));
$title .= "\n" . $suffix;

# This data array is designed to have a long label at 180 degrees, to
# make the label-to-pie-to-plot area edge space visible.
$label = str_repeat('123456790', (int)($label_size/10))
                  . substr('1234567890', 0, ($label_size % 10));
$data = array(
    array($label, 1),
    array($label, 2),
    array($label, 1),
);

$plot = new Phplot\Phplot\phplot(600, 400);
$plot->SetPlotType('pie');
$plot->SetDataType('text-data-single');
$plot->SetDataValues($data);
$plot->SetFontGD('generic', 5);
$plot->SetTitle($title);
$plot->SetPieLabelType('label');
$plot->SetPlotBorderType('full');
if (isset($min_size_factor)) $plot->pie_min_size_factor = $min_size_factor;
$plot->DrawGraph();
