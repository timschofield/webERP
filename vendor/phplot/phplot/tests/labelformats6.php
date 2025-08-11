<?php

# Test types of label formatting 6 (custom w/data labels)
require_once 'phplot.php';

# Decimal degrees to Degrees, Minutes, Seconds.
function deg_min_sec($value)
{
  if ($value >= 0) $sign = '';
  else {
    $sign = '-';
    $value *= -1;
  }
  $deg = (int)$value;
  $value = ($value - $deg) * 60;
  $min = (int)$value;
  $sec = (int)(($value - $min) * 60);
  return "$sign{$deg}d {$min}m {$sec}s";
}

$data = array();
for ($i = 0; $i < 15; $i++)
    $data[] = array('', 13 * $i + ($i / 14));
$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle("Label Format Test 6 with Y data labels");
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');
$p->SetXTickLabelPos('none');
$p->SetXTickPos('none');
$p->SetYTickIncrement(15);
$p->SetYDataLabelPos('plotin');
$p->SetPlotType('bars');
$p->SetYLabelType('custom', 'deg_min_sec');
$p->SetYTitle("Y: custom Deg/Min/Sec");
$p->DrawGraph();
