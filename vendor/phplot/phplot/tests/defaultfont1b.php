<?php

# Testing phplot - Default TT font (1b): No default path or font, SetFontTTF
require_once 'phplot.php';

$data = array(
  array('A', 3,  6),
  array('B', 2,  4),
  array('C', 1,  2),
);
$p = new Phplot\Phplot\phplot(800, 800);
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType('bars');
$p->SetTitle("TTF default not set, SetFontTTF() with no fontname\nTitles in 3 sizes");
$p->SetXTitle('X Axis Title');
$p->SetYTitle('Y Axis Title');
$p->SetFontTTF('title', '', 18);
$p->SetFontTTF('x_title', '', 14);
$p->SetFontTTF('y_title', '', 10);
$p->DrawGraph();
fwrite(STDERR, "OK defaultfont1b: title font=".$p->fonts['title']['font']."\n");
