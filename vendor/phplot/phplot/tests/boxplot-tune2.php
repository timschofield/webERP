<?php

# PHPlot test: Box plot - tuning parameters
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Increase max_width to 40 and fract_width to 0.49',
  'n_points' => 10,            # Number of data points
  'max_width' => 40,           # Tune: boxes_max_width
  'frac_width' => 0.49,        # Tune: boxes_frac_width
  );
require 'boxplot-tune.php';
