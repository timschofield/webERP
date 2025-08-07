<?php
# Typical bars with labels, for manual.
require_once 'phplot.php';

$data = array(
  array('First', 10),
  array('Second', 20),
  array('Third', 30),
);

$p = new Phplot\Phplot\phplot(400, 300);
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType('bars');
$p->SetTitle('Vertical Bar Plot With Labels');
$p->SetXTitle('Independent Variable');
$p->SetYTitle('Dependent Variable');
$p->SetXDataLabelPos('plotdown');
$p->SetXTickPos('none');
$p->SetYDataLabelPos('plotin');
$p->SetPlotAreaWorld(NULL, 0, NULL, 40);
$p->SetYTickIncrement(5);
$p->SetImageBorderType('plain');
$p->DrawGraph();
