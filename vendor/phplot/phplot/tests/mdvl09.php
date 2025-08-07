<?php

# Testing phplot - Data Value Labels on more plot types - case 9
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nSquared, 20px@45d",   # Title part 2
  'plot_type' => 'squared',  # Plot type
  'dvl_angle' => 45,      # Data Value Label angle, NULL to default
  'dvl_dist' => 20,       # Data Value Label distance, NULL to default
  );
require 'mdvl.php';
