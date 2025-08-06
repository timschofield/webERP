<?php

# Color callback - text-data plots, baseline
# This is the baseline script. Other scripts define the variables below,
# then include this script.
#    $plot_type : one of the types that accepts text-data (or text-data-yx)
#    $data_type : Don't set this for text-data; or set to text-data-yx
#    $subtitle  : Optional addition to title
# Scripts define a color callback function 'pick_color' to test the callback.

require_once 'phplot.php';

if (!isset($plot_type)) $plot_type = 'bars';
if (!isset($data_type)) $data_type = 'text-data';
$title = "Data Color Callback - $plot_type, $data_type";

$data = array(
    array('Jan', 10,  5),
    array('Feb', 11,  7),
    array('Mar', 13,  4),
    array('Apr', 16,  8),
    array('May', 20,  3),
    array('Jun', 25,  0),
);
$plot = new Phplot\Phplot\phplot(800, 600);
if (function_exists('pick_color')) {
    $title .= "\nWith data color callback";
    $plot->SetCallback('data_color', 'pick_color');
} else $title .= "\nDefault data colors";
if (isset($subtitle)) $title .= ' ' . $subtitle;
$plot->SetDataValues($data);
$plot->SetDataType($data_type);
$plot->SetPlotType($plot_type);
$plot->SetTitle($title);
$plot->SetXTickPos('none');
$plot->DrawGraph();
