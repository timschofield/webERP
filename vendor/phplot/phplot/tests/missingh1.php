<?php

# PHPlot test: Horizontal Lines with missing data - 1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (lines, DrawBrokenLines)",   # Title part 2
  'DBLines' => True,        # DrawBrokenLines: True or False or NULL to omit
  'PType' => 'lines',    # Plot Type
  'horizontal' => True, # Horizontal plot
  );
require 'missing.php';
