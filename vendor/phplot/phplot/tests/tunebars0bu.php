<?php

# PHPlot test: Bar chart tuning variables - baseline unshaded bars
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plot_type' => 'bars',
  'subtitle' => 'baseline',
  'shading' => FALSE,
  );
require 'tunebars.php';
