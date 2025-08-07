<?php

# PHPlot test: horizontal bars with data value labels, case 2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nNegative data with Y axis moved to 0",       # Title part 2
  'datasign' => -1,             # 1 for all >= 0, -1 for all <= 0, 0 for all
  'yaxis0' => True,             # Move Y axis to 0 if true
  );
require 'horzbar-label.php';
