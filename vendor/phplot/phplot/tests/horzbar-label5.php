<?php

# PHPlot test: horizontal bars with data value labels, case 5
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nBi-sign data, moved Y axis to 0, number format, 90d text",       # Title part 2
  'datasign' => 0,              # 1 for all >= 0, -1 for all <= 0, 0 for all
  'yaxis0' => True,             # Move Y axis to 0 if true
  'labelformat' => True,        # Format data labels ?
  'labelangle' => 90,           # X data label angle
  );
require 'horzbar-label.php';
