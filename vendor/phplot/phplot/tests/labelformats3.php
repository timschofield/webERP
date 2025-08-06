<?php

# Test types of label formatting 3 (data:2:suffix, data:1:suffix)
require_once 'phplot.php';
# This requires TTF to get the char set
require_once 'config.php';
$data = array();
for ($i = 0; $i < 10; $i++)
    $data[] = array('', 500 * $i, 1234 * $i);
$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTTFPath($phplot_test_ttfdir);
$p->SetDefaultTTFont($phplot_test_ttfonts['sansbold']);
$p->SetTitle("Label Format Test 3");
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');
$p->SetXTickIncrement(1000);
$p->SetYTickIncrement(1000);
$p->SetPlotType('lines');
$p->SetXLabelType('data', 2, '', "\xa2");
$p->SetXTitle("X: data(prec=2), suffix=cents sign \\xa2");
$p->SetYLabelType('data', 1, '', "\xe2\x82\xac");
$p->SetYTitle("Y: data(prec=1), suffix=euro sign \\xe2\\x82\\xac");
# Increase label size so we can see the funny symbols:
$p->SetFont('x_label', '', 10);
$p->SetFont('y_label', '', 10);
$p->DrawGraph();
