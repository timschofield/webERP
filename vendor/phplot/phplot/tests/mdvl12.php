<?php

# Testing phplot - Data Value Labels on more plot types - case 12
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nPoints, GD font 3 @ 270d, label @ 90d",   # Title part 2
  'dvl_angle' => 270,      # Data Value Label angle, NULL to default
  'yd_angle' => 90,       # Y Data Label angle, NULL to skip
  'y_label_font' => '3',   # Font for Y labels
  );
require 'mdvl.php';
