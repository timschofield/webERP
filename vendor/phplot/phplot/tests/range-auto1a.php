<?php

# PHPlot test: Plot auto-range test
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Zero Magnet threshold case (A): above, does not include 0',
  'min' => 83.34,          # Lowest bar value
  'max' => 100,            # Highest bar value
  );
require 'range-auto.php';
