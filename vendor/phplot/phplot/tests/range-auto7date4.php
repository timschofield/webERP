<?php

# PHPlot test: Plot auto-range test
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Date/time tick step - zero magnet, enabled',
  'min' => 10000,          # Lowest data value
  'max' => 30000,          # Highest data value
  'tick_mode' => 'date', # Tick selection mode
  );
require 'range-auto.php';
