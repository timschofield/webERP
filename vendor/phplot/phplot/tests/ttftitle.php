<?php

# PHPlot bug 1813071: Wrong title height for multi-line TTF text
# Note: This overlaps the title_text* tests somewhat, but with more fonts.
require_once 'phplot.php';
require 'config.php'; # Font info
$data = array(
  array('A', -3,  6),
  array('B', -2,  4),
  array('C', -1,  2),
  array('D',  0,  0),
  array('E',  1, -2),
  array('F',  2, -4),
  array('G',  3, -6),
);
$p = new Phplot\Phplot\phplot(800,800);

$p->SetTTFPath($phplot_test_ttfdir);
$p->SetDefaultTTFont($phplot_test_ttfonts['sans']);
$p->SetFont('title', $phplot_test_ttfonts['serifbolditalic'], 14);
$p->SetFont('x_title', $phplot_test_ttfonts['sansbold'], 10);
$p->SetFont('y_title', $phplot_test_ttfonts['serifbold'], 10);

$p->SetTitle("TrueType Text Title\nLine 2 of title\nLine 3 of title\nLine 4");
$p->SetXTitle("X Axis Tile\nLine 2\nLine 3\nLine 4");
$p->SetYTitle("Y Axis Tile\nLine 2\nLine 3\nLine 4");
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');
$p->SetXTickIncrement(1.0);
$p->SetYTickIncrement(1.0);
$p->SetPlotType('lines');
$p->DrawGraph();
