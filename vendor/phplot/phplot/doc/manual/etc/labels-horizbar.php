<?php
# Typical bars with labels, for manual.
require_once 'phplot.php';

$data = array(
  array('First', 10),
  array('Second', 20),
  array('Third', 30),
);

$p = new Phplot\Phplot\phplot(400, 300);
$p->SetDataType('text-data-yx');
$p->SetDataValues($data);
$p->SetPlotType('bars');
$p->SetTitle('Horizontal Bar Plot With Labels');
$p->SetXTitle('Dependent Variable');
$p->SetYTitle('Independent Variable');
$p->SetYDataLabelPos('plotleft');
$p->SetYTickPos('none');
$p->SetXDataLabelPos('plotin');
$p->SetPlotAreaWorld(0, NULL, 40, NULL);
$p->SetXTickIncrement(5);
$p->SetImageBorderType('plain');
$p->DrawGraph();
