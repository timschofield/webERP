<?php

# Multi-line data labels - baseline: TTf at 0 deg
require_once 'config.php';
$ttfont = $phplot_test_ttfdir . '/' . $phplot_test_ttfonts['sans'];

# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Multi-line data labels',
  'suffix' => '',           # Title part 2
  'font' => $ttfont,        # TrueType font file to use, or NULL for GD font
  'angle' => NULL,          # Label text angle
        ), $tp);
require_once 'phplot.php';

$data = array(
  array("AAA",         0,  6),
  array("BBB\nCC",     1,  4),
  array("CCC\nDD\nE",  2,  2),
  array("D",           3,  0),
  array("E\nFF\nGGG",  4,  2),
  array("FFF\nG\nHHH", 5,  4),
  array("G",           6,  6),
);

$p = new Phplot\Phplot\phplot(400,300);
if (isset($tp['font'])) {
  $p->SetDefaultTTFont($tp['font']);
}
$p->SetTitle($tp['title'] . $tp['suffix']);
$p->SetDataType('data-data');
$p->SetDataValues($data);

$p->SetXDataLabelPos('plotdown');
$p->SetXTickIncrement(1.0);
$p->SetYTickIncrement(1.0);
# Tick label and tick mark positions:
$p->SetXTickPos('xaxis');
$p->SetXTickLabelPos('none');
if (isset($tp['angle'])) $p->SetXLabelAngle($tp['angle']);
$p->SetPlotType('lines');
$p->DrawGraph();
