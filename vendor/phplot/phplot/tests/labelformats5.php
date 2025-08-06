<?php

# Test types of label formatting 5 - obsolete data_units_text
require_once 'phplot.php';
require_once 'config.php'; // Uses TTF for variety
$data = array();
for ($i = 0; $i < 10; $i++)
    $data[] = array('', 500 * $i, 1234 * $i);
$p = new Phplot\Phplot\phplot(800, 600);
$p->SetDefaultTTFont($phplot_test_ttfdir . $phplot_test_ttfonts['serif']);
$p->SetTitle("Label Format 5 with data_units_text=% suffix (obso)");
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');
$p->SetXTickIncrement(1000);
$p->SetYTickIncrement(1000);
# Make the labels bigger:
$p->SetFont('x_label', '', 9);
$p->SetFont('y_label', '', 9);
$p->SetPlotType('lines');
$p->data_units_text = '%';
$p->SetXLabelType('data', 1, '', '#');
$p->SetXTitle('X: data(prec=1), suffix=# and %');
$p->SetYLabelType('data', 1);
$p->SetYTitle('Y: data(prec=1), suffix=%');
$p->DrawGraph();
#fwrite(STDERR, print_r($p, True));
