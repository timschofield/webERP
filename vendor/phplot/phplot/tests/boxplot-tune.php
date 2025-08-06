<?php

# PHPlot test: Box plot - tuning parameters
require_once 'phplot.php';
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'subtitle' => 'Baseline',    # Title part 2
  'n_points' => 10,            # Number of data points
  'max_width' => NULL,         # Tune: boxes_max_width
  'min_width' => NULL,         # Tune: boxes_min_width
  'frac_width' => NULL,        # Tune: boxes_frac_width
  't_width' => NULL,           # Tune: boxes_t_width
        ), $tp);
require_once 'phplot.php';
# Extract all test parameters as local variables:
extract($tp);

$title = "Box Plot ($n_points points) Tuning\n$subtitle";

# Make a data array. Actual values does not matter, since
# this is only testing the widths of the boxes and Ts.
$data = array();
for ($i = 1; $i <= $n_points; $i++)
   $data[] = array('', 1, 5 + $i % 2, 10 + $i % 3, 15 + $i % 2, 19);

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle($title);
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType('boxes');
$p->SetXTickPos('none');
$p->SetXTickLabelPos('none');
$p->SetDrawYGrid(False);
# Tuning:
if (isset($max_width))  $p->boxes_max_width = $max_width;
if (isset($min_width))  $p->boxes_min_width = $min_width;
if (isset($frac_width)) $p->boxes_frac_width = $frac_width;
if (isset($t_width))    $p->boxes_t_width = $t_width;
$p->DrawGraph();
