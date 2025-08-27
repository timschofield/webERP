<?php

# Horizontal bars - case 6 options
# This is a parameterized test. See the script named at the bottom for details.
$tp = array_merge(array(
  'suffix' => "\n10x1, long labels, both sides, TTF @ 30d", # Title part 2
  'nrows' => 10,                # Number of bar groups
  'ncols' => 1,                 # Number of bars per group
  'longlabel' => True,          # Data variation: long data label
  'ydatalabelpos' => 'both',      # Y data label position (SetYDataLabelPos)
  'ydatalabelangle' => 30,     # Y data label angle
  'ttf' => True,               # Use all TTF text
  ));
require 'horzbar.php';
