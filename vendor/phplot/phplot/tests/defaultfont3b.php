<?php
# Testing phplot - Default TT font (3b): Set font with file basename only
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
$p->SetTitle("SetFontTTF() with file basename only");
$p->SetXTitle('X Axis Title');
$p->SetYTitle('Y Axis Title');
$p->SetFontTTF('title', $phplot_test_ttfonts['serifbold'], 18);
$p->SetFontTTF('x_title', $phplot_test_ttfonts['serifitalic'], 14);
$p->SetFontTTF('y_title', $phplot_test_ttfonts['serifbolditalic'], 10);
$p->DrawGraph();
fwrite(STDERR, "OK defaultfont3b: title font=".$p->fonts['title']['font']."\n");
