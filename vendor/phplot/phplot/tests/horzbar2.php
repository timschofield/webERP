<?php

# Horizontal bars - case 2 options
# This is a parameterized test. See the script named at the bottom for details.
$tp = array_merge(array(
  'suffix' => "\n5x5 unshaded with -ve values, y axis at 0",  # Title part 2
  'nrows' => 5,                 # Number of bar groups
  'ncols' => 5,                 # Number of bars per group
  'shade' => 0,                 # Bar shading, NULL to skip
  'negative' => 4,              # Make every nth data value negative
  'yaxis0' => True,             # Move Y axis to 0 if true
  'ydatalabelpos' => 'plotleft',  # Y data label position (SetYDataLabelPos)
  ));
require 'horzbar.php';
