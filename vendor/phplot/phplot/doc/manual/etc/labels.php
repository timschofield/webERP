<?php
# Bar chart with labels, for manual.
require_once 'phplot.php';

$data = array(
  array('Jan', 40),
  array('Feb', 30),
  array('Mar', 20),
  array('Apr', 10),
  array('May',  3),
  array('Jun',  7),
);

$p = new Phplot\Phplot\phplot(400,300);
$p->SetDataType('text-data');
$p->SetDataValues($data);

# Turn off X tick labels, use our data labels only:
$p->SetXTickLabelPos('none');
# Then we have to turn off the ticks too, because they aren't placed right:
$p->SetXTickPos('none');

$p->SetPlotType('bars');
$p->SetImageBorderType('plain');
$p->DrawGraph();
