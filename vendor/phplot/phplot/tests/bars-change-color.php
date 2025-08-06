<?php

# Testing phplot - Bars - change color.
# This is a trick, which almost works. The problem is the offset. The 2nd
# (and 3rd, if used) color bars are offset from their normal position.
require_once 'phplot.php';

$data = array(
  array('Jan', 40, ''),
  array('Feb', 30, ''),
  array('Mar', 20, ''),
  array('Apr', 10, ''),
  array('May',  3, ''),
  array('Jun',  7, ''),
  array('Jul', '', 10),
  array('Aug', 15, ''),
  array('Sep', 22, ''),
  array('Oct', 18, ''),
  array('Nov', '', 25),
  array('Dec', 14, ''),
);

$p = new Phplot\Phplot\phplot();
$p->SetTitle('Bars - Changing color trick');
$p->SetDataType('text-data');
$p->SetDataValues($data);

# Turn off X tick labels, use our data labels only:
$p->SetXTickLabelPos('none');
# Then we have to turn off the ticks too, because they aren't placed right:
$p->SetXTickPos('none');

$p->SetShading(0);
$p->SetPlotType('bars');
$p->DrawGraph();
