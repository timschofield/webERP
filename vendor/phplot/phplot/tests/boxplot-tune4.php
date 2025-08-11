<?php

# PHPlot test: Box plot - tuning parameters
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Increase min_width',
  'n_points' => 40,            # Number of data points
  'min_width' => 10,           # Tune: boxes_min_width
  );
require 'boxplot-tune.php';
