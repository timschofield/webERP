<?php

# Testing phplot - Default TT font (2b): Local path and named font.
# This test requires a specific font (see TEST_FONT) be present in the images/
# directory. The listed font is redistributable under the Open Fonts License.
require_once 'phplot.php';
define('TEST_FONT', 'FreeUniversal-Regular.ttf');

$data = array(
  array('A', 3,  6),
  array('B', 2,  4),
  array('C', 1,  2),
);
$p = new Phplot\Phplot\phplot(800, 800);
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType('bars');
$p->SetTitle('Local TTF path and SetFontTTF with file basename only');
$p->SetXTitle('X Axis Title');
$p->SetYTitle('Y Axis Title');
$p->SetTTFPath(getcwd() . DIRECTORY_SEPARATOR . 'images');
$p->SetFontTTF('title', TEST_FONT, 18);
$p->SetFontTTF('x_title', TEST_FONT, 14);
$p->SetFontTTF('y_title', TEST_FONT, 10);
$p->DrawGraph();
fwrite(STDERR, "OK defaultfont2b: title font=".$p->fonts['title']['font']."\n");
