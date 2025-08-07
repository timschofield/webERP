<?php

# PHPlot test: horizontal bars with data value labels, case 1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nNegative data, TTF labels, Number format",       # Title part 2
  'datasign' => -1,             # 1 for all >= 0, -1 for all <= 0, 0 for all
  'labelformat' => True,        # Format data labels ?
  'ttf' => True,                # Use TTF text for labels
  'shade' => 0,                 # Bar shading, NULL for none or 0 or >0
  );
require 'horzbar-label.php';
