<?php

# Testing phplot - tick/data label variant formatting - case 03
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'YT @ 45, YD @ 90 w/2dec',    # Title line 2
  'y_angle' => 45,        # Y Label angle, NULL to skip
  'yd_angle' => 90,       # Y Data Label angle, NULL to skip
  'yd_type' => 'data',        # Y labels: format type
  'yd_type_arg' => 2,    # Y labels: format type argument
  );
require 'labelvars.php';
