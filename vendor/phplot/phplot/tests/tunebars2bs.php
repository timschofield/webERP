<?php

# PHPlot test: Bar chart tuning variables - spread out shaded bars
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plot_type' => 'bars',
  'subtitle' => 'spread out bars',
  'bar_extra_space' => 0.1,
  'group_frac_width' => 0.9,
  'bar_width_adjust' => 0.7,
  );
require 'tunebars.php';
