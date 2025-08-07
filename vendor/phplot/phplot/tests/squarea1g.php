<?php

# PHPlot test: Squared and Squared Area (1g)
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Squared, Axis Data Labels',
  'plot_type' => 'squared',
  'dataset' => 3,
  'axis_data_labels' => TRUE,
  );
require 'squarea.php';
