<?php

# Testing phplot - Data Value Labels on more plot types - case 11
# This is a parameterized test. See the script named at the bottom for details.
require_once 'config.php'; // For TTF
$font = $phplot_test_ttfdir . $phplot_test_ttfonts['sans'];

$tp = array(
  'suffix' => "\nPoints, TTF @ 80d, label @ 45d",   # Title part 2
  'dvl_angle' => 45,      # Data Value Label angle, NULL to default
  'yd_angle' => 80,       # Y Data Label angle, NULL to skip
  'y_label_font' => $font,   # Font for Y labels
  'y_label_font_ttfsize' => 14,  # Use TTF font in this size if set
  );
require 'mdvl.php';
