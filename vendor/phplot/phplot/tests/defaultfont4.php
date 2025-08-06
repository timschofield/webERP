<?php

# Testing phplot - Default TT font (4): Default font with full path
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
$p->SetTitle("SetDefaultTTFont() with full path");
$p->SetDefaultTTFont($phplot_test_ttfdir . $phplot_test_ttfonts['mono']);
$p->SetXTitle('X Axis Title');
$p->SetYTitle('Y Axis Title');
$p->DrawGraph();
fwrite(STDERR, "OK defaultfont4: title font=".$p->fonts['title']['font']."\n");
