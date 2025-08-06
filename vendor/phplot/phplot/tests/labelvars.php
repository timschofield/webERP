<?php

# Testing phplot - tick/data label variant formatting - baseline
# This tests the use of angle, format, and position controls on  X and Y data
# and tick labels, using features added after PHPlot-5.0.7.
# For testing Y tick vs Y data labels, a bar chart is used (parameter 'x'
# is false).
# For testing X tick vs X data labels, a linepoints chart is used (parameter
# 'x' is true).

# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'suffix' => 'baseline',   # Title line 2
  'x' => False,             # Chart type, explicit X values or not

  'x_angle' => NULL,        # X Label angle, NULL to skip
  'xd_angle' => NULL,       # X Data Label angle, NULL to skip
  'x_type' => NULL,         # X Labels: format type
  'x_type_arg' => NULL,     # X Labels: format type argument
  'xd_type' => NULL,        # X Data Labels: format type
  'xd_type_arg' => NULL,    # X Data Labels: format type argument
  'xt_pos' => NULL,         # X Tick Label position, NULL to skip
  'xd_pos' => NULL,         # X Data Label position, NULL to skip

  'y_angle' => NULL,        # Y Label angle, NULL to skip
  'yd_angle' => NULL,       # Y Data Label angle, NULL to skip
  'y_type' => NULL,         # Y Labels: format type
  'y_type_arg' => NULL,     # Y Labels: format type argument
  'yd_type' => NULL,        # Y Data Labels: format type
  'yd_type_arg' => NULL,    # Y Data Labels: format type argument
  'yt_pos' => NULL,         # Y Tick Label position, NULL to skip

        ), $tp);
require_once 'phplot.php';
# Needed for TrueType fonts, so text angles can be set.
require_once 'config.php';

# Y values: Need to include different amounts of decimals,
# to validate the formatting options. Labels are filled in below.
$values = array(
   0.15, 15.20, 35.90, 45.49, 75.20, 99.00,
  97.80, 80.00, 70.02, 55.00, 20.70,  0.10
);

# Build the data array for implicit or explicit X values:
if ($tp['x']) {
  # Explicit X, for linepoints plot
  $plot_type = 'linepoints';
  $data_type = 'data-data';
  $title = 'Linepoints chart with X label control variations';
  $enable_y_data_labels = FALSE;

  for ($i = 0; $i < 12; $i++)
     $data[$i] = array( @strftime('%b', mktime(12, 0, 0, $i + 1, 1)),
          $i, $values[$i]);

} else {
  # Implicit X, for bars plot
  $plot_type = 'bars';
  $data_type = 'text-data';
  $title = 'Bar chart with Y label format variations';
  $enable_y_data_labels = TRUE;

  for ($i = 0; $i < 12; $i++)
     $data[$i] = array( @strftime('%b', mktime(12, 0, 0, $i + 1, 1)),
          $values[$i]);
}

$p = new Phplot\Phplot\phplot(1024, 768);
$p->SetTitle($title . "\n" . $tp['suffix']);
$p->SetDataType($data_type);
$p->SetDataValues($data);
$p->SetPlotType($plot_type);
$p->SetXTickIncrement(1);
$p->SetYTickIncrement(10);
if ($enable_y_data_labels) $p->SetYDataLabelPos('plotin');
$p->SetPlotAreaWorld(NULL, 0, NULL, 109);
# Use this TrueType font and make labels bigger:
$p->SetDefaultTTFont($phplot_test_ttfdir . $phplot_test_ttfonts['sans']);
$p->SetFont('x_label', '', 10);
$p->SetFont('y_label', '', 10);

# Options: X
if (isset($tp['x_angle']))
    $p->SetXLabelAngle($tp['x_angle']);
if (isset($tp['xd_angle']))
    $p->SetXDataLabelAngle($tp['xd_angle']);
if (isset($tp['x_type']))
    $p->SetXLabelType($tp['x_type'], $tp['x_type_arg']);
if (isset($tp['xd_type']))
    $p->SetXDataLabelType($tp['xd_type'], $tp['xd_type_arg']);
if (isset($tp['xt_pos']))
    $p->SetXTickLabelPos($tp['xt_pos']);
if (isset($tp['xd_pos']))
    $p->SetXDataLabelPos($tp['xd_pos']);

# Options: Y
if (isset($tp['y_angle']))
    $p->SetYLabelAngle($tp['y_angle']);
if (isset($tp['yd_angle']))
    $p->SetYDataLabelAngle($tp['yd_angle']);
if (isset($tp['y_type']))
    $p->SetYLabelType($tp['y_type'], $tp['y_type_arg']);
if (isset($tp['yd_type']))
    $p->SetYDataLabelType($tp['yd_type'], $tp['yd_type_arg']);
if (isset($tp['yt_pos']))
    $p->SetYTickLabelPos($tp['yt_pos']);

$p->DrawGraph();
