<?php

# PHPlot test: Squared and Squared Area (2e)
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Squared Area, Borders, Axis Data Labels',
  'plot_type' => 'squaredarea',
  'dataset' => 2,
  'borders' => TRUE,
  'axis_data_labels' => TRUE,
  );
require 'squarea.php';
