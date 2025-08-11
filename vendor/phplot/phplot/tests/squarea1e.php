<?php

# PHPlot test: Squared and Squared Area (1e)
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Squared, Axis & Data Value Labels',
  'plot_type' => 'squared',
  'dataset' => 1,
  'axis_data_labels' => TRUE,
  'data_value_labels' => TRUE,
  );
require 'squarea.php';
