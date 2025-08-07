<?php

# PHPlot test: horizontal bars with data value labels, case 4
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nBi-sign data, moved Y axis to 0, deep shading",       # Title part 2
  'datasign' => 0,              # 1 for all >= 0, -1 for all <= 0, 0 for all
  'yaxis0' => True,             # Move Y axis to 0 if true
  'shade' => 10,                # Bar shading, NULL for none or 0 or >0
  );
require 'horzbar-label.php';
