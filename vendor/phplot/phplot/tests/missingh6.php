<?php

# PHPlot test: Horizontal Lines with missing data - 6
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (Missing first/last, DrawBrokenLines)",   # Title part 2
  'DBLines' => True,    # DrawBrokenLines: True or False or NULL to omit
  'xmiss1' => 0,        # First X|Y (0-14 or NULL) for which Y|X is missing
  'xmiss2' => 14,       # Second X|Y (0-14 or NULL) for which Y|X is missing
  'horizontal' => True, # Horizontal plot
  );
require 'missing.php';
