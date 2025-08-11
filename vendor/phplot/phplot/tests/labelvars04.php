<?php

# Testing phplot - tick/data label variant formatting - case 04
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'YT:-30d, 5.1f; YD:60d, 3 dec',    # Title line 2
  'y_angle' => -30,        # Y Label angle, NULL to skip
  'yd_angle' => 60,       # Y Data Label angle, NULL to skip
  'y_type' => 'printf',     # Y labels: format type
  'y_type_arg' => '%5.1f',     # Y labels: format type argument
  'yd_type' => 'data',        # Y labels: format type
  'yd_type_arg' => 3,    # Y labels: format type argument
  );
require 'labelvars.php';
