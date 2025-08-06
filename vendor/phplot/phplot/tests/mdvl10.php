<?php

# Testing phplot - Data Value Labels on more plot types - case 10
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nPoints, Different tick/data label formats",   # Title part 2
  'y_type' => 'data',       # Y Labels: format type
  'y_type_arg' => 4,        # Y Labels: format type argument
  'yd_type' => 'data',      # Y Data Labels: format type
  'yd_type_arg' => 1,       # Y Data Labels: format type argument
  );
require 'mdvl.php';
