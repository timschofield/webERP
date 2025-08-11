<?php

# Test types of label formatting 1 (data:2, printf:%e)
require_once 'phplot.php';
$data = array();
for ($i = 0; $i < 10; $i++)
    $data[] = array('', 500 * $i, 1234 * $i);
$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle("Label Format Test 1");
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');
$p->SetXTickIncrement(1000);
$p->SetYTickIncrement(1000);
$p->SetPlotType('lines');
$p->SetXTitle("X: data, prec=2");
$p->SetXLabelType('data', 2);
$p->SetYTitle("Y: printf %8.2e");
$p->SetYLabelType('printf', '%8.2e');
$p->DrawGraph();
