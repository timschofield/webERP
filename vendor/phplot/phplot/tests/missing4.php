<?php

# PHPlot test: Lines with missing data - 4
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (linepoints, No BrokenLines)",   # Title part 2
  'DBLines' => False,       # DrawBrokenLines: True or False or NULL to omit
  'PType' => 'linepoints',  # Plot Type: lines, linepoints, squared
  );
require 'missing.php';
