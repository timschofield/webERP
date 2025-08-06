<?php

# Color callback - data-data-error plots, baseline
# This is the baseline script. Other scripts define the variables below,
# then include this script.
#    $plot_type : one of the types that accepts text-data (or text-data-yx)
#    $data_type : (only supports the default data-data-error)
#    $subtitle  : Optional addition to title
# Scripts define a color callback function 'pick_color' to test the callback.

require_once 'phplot.php';

if (!isset($plot_type)) $plot_type = 'lines';
if (!isset($data_type)) $data_type = 'data-data-error';
$title = "Data Color Callback - $plot_type, $data_type";

$data = array(
    array('', 0, 10, 2, 2,   5, 2, 2),
    array('', 1, 11, 2, 1,   7, 1, 1),
    array('', 2, 13, 1, 1,   4, 1, 2),
    array('', 3, 16, 1, 2,   8, 2, 1),
    array('', 4, 20, 3, 0,   3, 2, 1),
    array('', 5, 25, 0, 2,   0, 2, 0),
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
$plot->DrawGraph();
