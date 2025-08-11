<?php

# PHPlot test: horizontal bars with data value labels, case 6
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nBi-sign data, moved Y axis to 0, 45d TTF text",       # Title part 2
  'datasign' => 0,              # 1 for all >= 0, -1 for all <= 0, 0 for all
  'yaxis0' => True,             # Move Y axis to 0 if true
  'labelangle' => 45,           # X data label angle
  'ttf' => True,                # Use TTF text for labels
  );
require 'horzbar-label.php';
