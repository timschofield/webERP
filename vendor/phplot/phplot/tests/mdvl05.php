<?php

# Testing phplot - Data Value Labels on more plot types - case 5
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nLines, 20px@0d",   # Title part 2
  'plot_type' => 'lines',  # Plot type
  'dvl_angle' => 0,      # Data Value Label angle, NULL to default
  'dvl_dist' => 20,       # Data Value Label distance, NULL to default
  );
require 'mdvl.php';
