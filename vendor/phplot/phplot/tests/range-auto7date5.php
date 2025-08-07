<?php

# PHPlot test: Plot auto-range test
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Date/time tick step - zero magnet disabled',
  'min' => 10000,          # Lowest data value
  'max' => 30000,          # Highest data value
  'zm' => 0,               # Range zero magnet
  'tick_mode' => 'date', # Tick selection mode
  );
require 'range-auto.php';
