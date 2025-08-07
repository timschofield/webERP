<?php

# Testing phplot - Data Value Labels on more plot types - master
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Data Value Labels on More Plot Types',  # First or only line
  'suffix' => "\nPoints, default position",   # Title part 2
  'plot_type' => 'points',  # Plot type
  'dvl_angle' => NULL,      # Data Value Label angle, NULL to default
  'dvl_dist' => NULL,       # Data Value Label distance, NULL to default
  'y_type' => NULL,         # Y Labels: format type
  'y_type_arg' => NULL,     # Y Labels: format type argument
  'yd_angle' => NULL,       # Y Data Label angle, NULL to skip
  'yd_type' => 'data',      # Y Data Labels: format type
  'yd_type_arg' => 2,       # Y Data Labels: format type argument
  'y_label_font' => NULL,   # Font for Y labels
  'y_label_font_ttfsize' => NULL,  # Use TTF font in this size if set
        ), $tp);
require_once 'phplot.php';

extract($tp);

// Build a data array using 1 cycle of sin() so it gets different slopes.
$n_pts = 20;
$r = 10.0;
$theta = 0.0;
$d_theta = 2 * M_PI / $n_pts;
$data = array();
for ($i = 0; $i <= $n_pts; $i++) { // 1 extra point to return to 0
    $data[] = array('', $theta, $r * sin($theta));
    $theta += $d_theta;
}

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle($title . $suffix);
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetImageBorderType('solid');
$p->SetYDataLabelPos('plotin');
if (isset($yd_angle)) $p->SetYDataLabelAngle($yd_angle);
if (isset($y_type)) $p->SetYLabelType($y_type, $y_type_arg);
if (isset($yd_type)) $p->SetYDataLabelType($yd_type, $yd_type_arg);
if (isset($y_label_font)) {
    if (isset($y_label_font_ttfsize))
        $p->SetFontTTF('y_label', $y_label_font, $y_label_font_ttfsize);
    else
        $p->SetFontGD('y_label', $y_label_font);
}

$p->SetXLabelType('data', 3);
$p->SetPlotType($plot_type);
if (isset($dvl_angle)) $p->data_value_label_angle = $dvl_angle;
if (isset($dvl_dist)) $p->data_value_label_distance = $dvl_dist;
$p->DrawGraph();
