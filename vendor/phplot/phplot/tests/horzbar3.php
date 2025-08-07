<?php

# Horizontal bars - case 3 options
# This is a parameterized test. See the script named at the bottom for details.
$tp = array_merge(array(
  'suffix' => "\nDeep shade, right labels",       # Title part 2
  'shade' => 5,                     # Bar shading, NULL to skip
  'ydatalabelpos' => 'plotright',  # Y data label position (SetYDataLabelPos)
  ));
require 'horzbar.php';
