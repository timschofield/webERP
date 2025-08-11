<?php

# PHPlot bug 1813021 - Miss-positioned right-just. vert. text
# PHPlot < 5.0.4 did not position 90 degree GD text correctly
# for Right horizontal justification.
# Only Y tick labels can be 90 degrees with right justification.

require_once 'phplot.php';
$data = array(
  array('A', -3,  60),
  array('B', -2,  40),
  array('C', -1,  20),
  array('D',  0,  0),
  array('E',  1, -20),
  array('F',  2, -40),
  array('G',  3, -60),
);
$p = new Phplot\Phplot\phplot(800,600);
$p->SetTitle('Y Tick Labels at 90 degrees');
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');
$p->SetXTickIncrement(1.0);
$p->SetYTickIncrement(10);
$p->SetYLabelAngle(90);
$p->SetPlotType('lines');
$p->DrawGraph();
