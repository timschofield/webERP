<?php

# PHPlot test: Plot auto-range test
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'End adjust threshold - case (D), decrease adjust, stops at 100',
  'min' => 1,              # Lowest data value
  'max' => 98,             # Highest data value
  'adjust_amount' => 0.01, #  % of range for adjustment
  );
require 'range-auto.php';
