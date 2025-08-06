<?php

# PHPlot test: Plot auto-range test
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'End adjust threshold - case (C), increase adjust, extends to 110',
  'min' => 1,              # Lowest data value
  'max' => 97,             # Highest data value
  'adjust_amount' => 0.05, #  % of range for adjustment
  );
require 'range-auto.php';
