<?php

# Testing phplot - Default TT font (5): Default path and default font
require_once 'phplot.php';
require_once 'config.php'; // TTF setup

$data = array(
  array('A', 3,  6),
  array('B', 2,  4),
  array('C', 1,  2),
);
$p = new Phplot\Phplot\phplot(800, 800);
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType('bars');
$p->SetTitle("SetTTFPath and SetDefaultTTFont");
$p->SetTTFPath($phplot_test_ttfdir);
$p->SetDefaultTTFont($phplot_test_ttfonts['sansitalic']);
$p->SetXTitle('X Axis Title');
$p->SetYTitle('Y Axis Title');
$p->DrawGraph();
fwrite(STDERR, "OK defaultfont5: title font=".$p->fonts['title']['font']."\n");
