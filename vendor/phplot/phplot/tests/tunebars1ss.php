<?php
# PHPlot test: Bar chart tuning variables - full width shaded stackedbars
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plot_type' => 'stackedbars',
  'subtitle' => 'full width bars',
  'bar_extra_space' => 0,
  'group_frac_width' => 1.0,
  );
require 'tunebars.php';
