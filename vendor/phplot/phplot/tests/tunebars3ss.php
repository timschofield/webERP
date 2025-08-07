<?php

# PHPlot test: Bar chart tuning variables - narrower shaded stackedbars
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plot_type' => 'stackedbars',
  'subtitle' => 'narrower bars',
  'bar_width_adjust' => 0.7,
  );
require 'tunebars.php';
