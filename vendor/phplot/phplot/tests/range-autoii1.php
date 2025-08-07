<?php

# PHPlot test: Plot auto-range test
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Integer Tick Increment : has < MinTicks tick intervals',
  'min' => 0,              # Lowest data value
  'max' => 4.8,               # Highest data value
  'tick_mode' => 'decimal', # Tick selection mode
  'intinc' => TRUE,        # Integer increment flag
  'plot_type' => 'squared', # Plot type, must work with data-data
  );
require 'range-auto.php';
