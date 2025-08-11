<?php

# Testing phplot - Candlestick. Variable number of points. Testing widths.
require_once 'phplot.php';

if (empty($n)) $n = 20;
if (empty($width)) $width = 800;
if (empty($height)) $height = 600;
if (empty($plot_type)) $plot_type = 'candlesticks';
# $line_widths can be set, defaults to NULL to omit.

# Build Open, High, Low, Close data. Values are not important - just want
# to see the widths. Need some up and some down.
$data = array();
$v = 0;
for ($i = 0; $i < $n; $i++) {
    if ($i % 2) {
        $close = $v + 6;
        $open = $v + 4;
    } else {
        $open = $v + 6;
        $close = $v + 4;
    }
    $data[] = array('', $open, $v + 8, $v + 2, $close);
    $v += 0.1;
}

$title = "OHLC size/density Test Plot\n$plot_type type, $n points";
if (!empty($line_widths))
    $title .= ", line_widths=(" . implode(',', $line_widths) . ")";

$p = new Phplot\Phplot\phplot($width, $height);
$p->SetTitle($title);
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType($plot_type);
$p->SetPlotAreaWorld(NULL, 0, NULL, NULL);
if (!empty($line_widths)) $p->SetLineWidths($line_widths);
$p->SetXTickPos('none');
$p->SetXTickLabelPos('none');
$p->DrawGraph();
