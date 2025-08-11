<?php

# Testing phplot - OHLC - all up/down combos - candlesticks
if (empty($plot_type)) $plot_type = 'candlesticks';
require_once 'phplot.php';

/*
Testing all cases as follows. There are 16 combinations but 4 duplicates
L = Low, O = Open, C = Close, H = High.
              Data order:  Open, High, Low, Close
*/
$data = array(
    array('L < O < C < H',   2,    4,   1,     3),
    array('L < O < C = H',   2,    3,   1,     3),
    array('L < O = C < H',   2,    3,   1,     2),
    array('L < O = C = H',   2,    2,   1,     2),
    array('L = O < C < H',   1,    3,   1,     2),
    array('L = O < C = H',   1,    2,   1,     2),
    array('L = O = C < H',   1,    2,   1,     1),
    array('L = O = C = H',   1,    1,   1,     1),
    array('L < C < O < H',   3,    4,   1,     2),
    array('L < C < O = H',   3,    3,   1,     2),
    array('L < C = O < H',   2,    3,   1,     2),
    array('L < C = O = H',   2,    2,   1,     2),
    array('L = C < O < H',   2,    3,   1,     1),
    array('L = C < O = H',   2,    2,   1,     1),
    array('L = C = O < H',   1,    2,   1,     1),
    array('L = C = O = H',   1,    1,   1,     1),
);
$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle("OHLC Plots, All Data Order Cases\nPlot type: $plot_type");
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType($plot_type);
$p->SetXTickPos('none');
$p->SetXDataLabelAngle(90);
$p->SetPlotAreaWorld(NULL, 0, NULL, 5);
$p->SetDrawYGrid(False);
$p->SetLineWidths(array(2,3));
$p->DrawGraph();
