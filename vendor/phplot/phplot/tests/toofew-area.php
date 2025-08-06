<?php

# Testing phplot - Area plot with too few X values
require_once 'phplot.php';
if (!isset($plottype)) $plottype = 'area';
$data = array(array('A',  5, 4, 3));
$p = new Phplot\Phplot\phplot();
$p->SetTitle("$plottype plot with too few X values");
$p->SetDataValues($data);
$p->SetDataType('text-data');
$p->SetPlotType($plottype);
$p->DrawGraph();
