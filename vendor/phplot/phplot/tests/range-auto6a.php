<?php

# PHPlot test: Plot auto-range test
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Y Axis Extends Across Zero Problem - Baseline',
  'min' => 5,              # Lowest data value
  'max' => 100,            # Highest data value
  'zm' => 0,               # Range zero magnet
  'tickanchor' => NULL,    # Tick anchor, if set
  );
require 'range-auto.php';
