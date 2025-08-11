<?php

# Testing phplot - Data Value Labels on more plot types - case 7
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nLinepoints, 2px@225",   # Title part 2
  'plot_type' => 'linepoints',  # Plot type
  'dvl_angle' => 225,      # Data Value Label angle, NULL to default
  'dvl_dist' => 2,       # Data Value Label distance, NULL to default
  );
require 'mdvl.php';
