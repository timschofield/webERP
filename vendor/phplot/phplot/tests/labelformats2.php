<?php

# Test types of label formatting 2 (custom, data:3)
require_once 'phplot.php';

function my_format($val, $arg)
{
  $m = (int)($val / 60);
  $h = (int)($m / 60);
  $m %= 60;
  return sprintf("%dH%dM", $h, $m);
}

$data = array();
for ($i = 0; $i < 10; $i++)
    $data[] = array('', 4000 * $i, 1234 * $i);
$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle("Label Format Test 2");
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');
$p->SetXTickIncrement(2000);
$p->SetYTickIncrement(1000);
$p->SetPlotType('lines');
$p->SetXLabelType('custom', 'my_format');
$p->SetXTitle("X: custom H:M");
$p->SetYLabelType('data', 3);
$p->SetYTitle("Y: data, prec=3");
$p->DrawGraph();
