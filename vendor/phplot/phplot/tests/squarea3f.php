<?php

# PHPlot test: Squared and Squared Area (3f)
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Stacked Squared Area, Axis Data Labels',
  'plot_type' => 'stackedsquaredarea',
  'dataset' => 3,
  'axis_data_labels' => TRUE,
  );
require 'squarea.php';
