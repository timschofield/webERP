<?php

# Testing phplot - Data Value Labels on more plot types - case 8
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nSquared, @135d",   # Title part 2
  'plot_type' => 'squared',  # Plot type
  'dvl_angle' => 135,      # Data Value Label angle, NULL to default
  );
require 'mdvl.php';
