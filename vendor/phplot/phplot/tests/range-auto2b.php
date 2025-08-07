<?php

# PHPlot test: Plot auto-range test
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'End adjust threshold - case (B), increase to 110',
  'min' => 1,              # Lowest data value
  'max' => 98,             # Highest data value
  );
require 'range-auto.php';
