<?php

# Testing phplot - Data Value Labels on more plot types - case 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nPoints, 2px@180",   # Title part 2
  'plot_type' => 'points',  # Plot type
  'dvl_angle' => 180,      # Data Value Label angle, NULL to default
  'dvl_dist' => 2,       # Data Value Label distance, NULL to default
  );
require 'mdvl.php';
