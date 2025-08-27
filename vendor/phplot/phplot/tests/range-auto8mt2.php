<?php

# PHPlot test: Plot auto-range test
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Min Ticks Variations',
  'min' => 0,              # Lowest data value
  'max' => 95,             # Highest data value
  'mintick' => 4,          # Minimum tick count
  'plot_type' => 'points', # Plot type, must work with data-data
  'n' => 50,               # Number of data points
  );
require 'range-auto.php';
