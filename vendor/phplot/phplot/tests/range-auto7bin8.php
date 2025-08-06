<?php

# PHPlot test: Plot auto-range test
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Binary tick step: very small range',
  'min' => 0,              # Lowest data value
  'max' => 0.02,           # Highest data value
  'tick_mode' => 'binary', # Tick selection mode
  );
require 'range-auto.php';
