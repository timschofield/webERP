<?php

# Testing phplot - Tick marks at data points (requires phplot >= 5.4.0)
require_once 'phplot.php';
$data = array();
for ($i = 0; $i < 20; $i++) $data[] = array("Pt:$i", $i * $i);
$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle('X Tick Mark and Data Label Alignment');
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType('lines');
$p->SetXTickIncrement(1);
$p->SetXTickAnchor(0.5);
$p->SetDrawXGrid(True);
$p->SetPlotAreaWorld(0.5);
$p->DrawGraph();
