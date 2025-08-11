<?php

# Testing phplot - Data Value Labels on more plot types - case 2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nPoints, 20px@270d",   # Title part 2
  'plot_type' => 'points',  # Plot type
  'dvl_angle' => 270,      # Data Value Label angle, NULL to default
  'dvl_dist' => 20,       # Data Value Label distance, NULL to default
  );
require 'mdvl.php';
