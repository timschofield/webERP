<?php

# Horizontal bars - case 5 options
# This is a parameterized test. See the script named at the bottom for details.
$tp = array_merge(array(
  'suffix' => "\nLong labels, -ve vals, left axis, right labels",       # Title part 2
  'longlabel' => True,         # Data variation: long data label
  'negative' => 3,              # Make every nth data value negative
  'ydatalabelpos' => 'plotright',      # Y data label position (SetYDataLabelPos)
  ));
require 'horzbar.php';
