<?php

# PHPlot test: Plot auto-range test
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Binary tick step',
  'min' => 0,              # Lowest data value
  'max' => 1000000,        # Highest data value
  'tick_mode' => 'binary', # Tick selection mode
  );
require 'range-auto.php';
