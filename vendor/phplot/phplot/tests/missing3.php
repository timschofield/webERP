<?php

# PHPlot test: Lines with missing data - 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (squared, DrawBrokenLines)",   # Title part 2
  'DBLines' => True,        # DrawBrokenLines: True or False or NULL to omit
  'PType' => 'squared',  # Plot Type: lines, linepoints, squared
  );
require 'missing.php';
