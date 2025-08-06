<?php
# PHPlot test: Squared and Squared Area (2f)
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Squared Area, Axis Data Labels',
  'plot_type' => 'squaredarea',
  'dataset' => 3,
  'axis_data_labels' => TRUE,
  );
require 'squarea.php';
