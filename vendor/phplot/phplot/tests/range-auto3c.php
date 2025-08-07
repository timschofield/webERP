<?php

# PHPlot test: Plot auto-range test
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle'    => 'Range Sign Case C: min < 0 < max',
  'min' => -598,           # Lowest data value
  'max' => 598,            # Highest data value
  'zm' => 0,               # Range zero magnet
  );
require 'range-auto.php';
