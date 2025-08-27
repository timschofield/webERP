<?php

# PHPlot test: Squared and Squared Area (1i)
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Squared, Draw Broken Lines',
  'plot_type' => 'squared',
  'dataset' => 3,
  'broken_lines' => TRUE,
  'missing_point' => TRUE,
  );
require 'squarea.php';
