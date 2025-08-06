<?php

# PHPlot test - empty plot with no Y values at all
# Note: This differs from empty-plot.php, which has a data
# array containing y='' for each row, column. This test has
# a data array with no Y values at all.
# The purpose of this test is to check for a bug with axis data labels.
# Some plot types would not display the labels if there is no data at all.
#
# This is a parameterized test. Set plot_type, optionally data_type and
# call this script.
require_once 'phplot.php';

if (empty($plot_type)) $plot_type = 'lines';
if (empty($data_type)) $data_type = 'text-data';

$need_x = ($data_type == 'data-data' || $data_type == 'data-data-yx'
       || $data_type == 'data-data-error' || $data_type == 'data-data-xyz');

# Build a data array with no Y values at all:
$data = array();
for ($r = 0; $r < 8; $r++) {
  $row = array('='.chr(ord('A')+$r).'='); // Label
  if ($need_x) $row[] = $r + 1;  // Independent variable
  $data[] = $row;
}

$p = new Phplot\Phplot\phplot();
$p->SetTitle("Plot with no Y values: $plot_type, $data_type\n"
           . "Check for horizontal axis data labels");
$p->SetPlotType($plot_type);
$p->SetDataType($data_type);
$p->SetDataValues($data);
$p->DrawGraph();
