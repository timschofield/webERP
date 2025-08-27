<?php

# PHPlot test: Plot auto-range test
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Null Range, positive value',
  'min' => 10,             # Lowest data value
  'max' => 10,             # Highest data value
  );
require 'range-auto.php';
