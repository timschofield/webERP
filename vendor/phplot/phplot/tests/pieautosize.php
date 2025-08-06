<?php

# PHPlot test - Pie Chart Sizing - auto vs manual - baseline
require_once 'phplot.php';

# Set defaults; can be overridden in a script that includes this one.
if (!isset($n_slices)) $n_slices = 12;
if (!isset($pie_autosize)) $pie_autosize = True;
# $label_pos = NULL; // Null or unset to skip SetLabelScalePosition()

# Build a data array:
$data = array();
for ($v = 1, $i = 0; $i < $n_slices; $i++, $v *= 1.414) $data[] = array('', $v);

# Build a title:
$title = 'Pie Autosize Test: Label scale pos='
       . (isset($label_pos) ? $label_pos : "not set")
       . ', Autosize is ';
# Detect presence of autosize feature (PHPlot>=5.6.0):
$do_autosize = method_exists('Phplot\Phplot\phplot', 'SetPieAutoSize');
if (!$do_autosize) {
    $title .= "(not available).\n"
            . "Pie should be sized right up to plot area edge.";
} elseif ($pie_autosize) {
    $title .= "On.\n"
            . "Pie should be sized so labels are contained in plot area.";
} else {
    $title .= "Off.\n"
            . "Pie should be sized just inside (5px) of plot area.";
}

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetPlotType('pie');
$plot->SetDataType('text-data-single');
$plot->SetDataValues($data);
$plot->SetTitle($title);
$plot->SetMarginsPixels(50, 50, 50, 50);
$plot->SetShading(0);
if (isset($label_pos)) $plot->SetLabelScalePosition($label_pos);
// Turn on plot area background to make margins visible.
$plot->SetPlotBgColor('gray');
$plot->SetDrawPlotAreaBackground(TRUE);
if ($do_autosize) $plot->SetPieAutoSize($pie_autosize);
$plot->DrawGraph();
