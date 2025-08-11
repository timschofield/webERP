<?php

# Testing phplot - All kinds of text
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Text Tests',
  'suffix' => " (default behavior)", # Title part 2
  'use_ttf' => False,  # If true use TT fonts
  'use_gdf' => False,  # If true use GD fonts
    # If neither use_ttf nor use_gdf are set, let it all default.
  'x_label_angle' => NULL,  # SetXLabelAngle
  'y_label_angle' => NULL,  # SetYLabelAngle
        ), $tp);
require_once 'phplot.php';


# Contains font settings:
require 'config.php';

$p = new Phplot\Phplot\phplot(800,600);
$data = array(
  array('A POINT LABEL', 10, 9, 8),
  array('B POINT LABEL', 20, 19, 18),
  array('C POINT LABEL', 30, 28, 26),
);
$p->SetPlotType('bars');
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotBorderType('full');

if ($tp['use_ttf']) {
  # Fonts:
  $p->SetTTFPath($phplot_test_ttfdir);
  $p->SetDefaultTTFont($phplot_test_ttfonts['sans']);
  $p->SetFont('legend', $phplot_test_ttfonts['serifitalic'],  10);
  $p->SetFont('title', $phplot_test_ttfonts['sansbold'],   20);
  $p->SetFont('x_title', '', 12);
  $p->SetFont('y_title', '', 12);
  $p->SetFont('x_label', '', 12);
  $p->SetFont('y_label', '', 12);
  $p->SetFont('generic', '', 14);
} elseif ($tp['use_gdf']) {
  $p->SetFont('legend', '4');
  $p->SetFont('title', '5');
  $p->SetFont('x_title', '3');
  $p->SetFont('y_title', '3');
  $p->SetFont('x_label', '2');
  $p->SetFont('y_label', '2');
  $p->SetFont('generic', '2');
}

# All axis, all labels, both sides
$p->SetTitle($tp['title'] . $tp['suffix']);
$p->SetLegend(array('Legend 1', 'Legend 2', 'Legend 3'));
$p->SetXTitle('X Axis Title Here', 'both');
$p->SetYTitle('Y Axis Title Here', 'both');
$p->SetXTickLabelPos('none');
$p->SetXTickPos('none');
$p->SetXDataLabelPos('both');
$p->SetYTickLabelPos('both');
$p->SetYTickPos('both');
$p->SetYDataLabelPos('plotin');

if (isset($tp['x_label_angle']))
  $p->SetXLabelAngle($tp['x_label_angle']);
if (isset($tp['y_label_angle']))
  $p->SetYLabelAngle($tp['y_label_angle']);

$p->DrawGraph();
