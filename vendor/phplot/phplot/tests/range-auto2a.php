<?php

# PHPlot test: Plot auto-range test
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'End adjust threshold - case (A), stops at 100',
  'min' => 1,              # Lowest data value
  'max' => 97,             # Highest data value
  );
require 'range-auto.php';
