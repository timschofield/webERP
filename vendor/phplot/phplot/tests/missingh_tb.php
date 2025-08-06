<?php

# PHPlot test: Horizontal Plots with missing data - plot type thinbarline
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (thinbarline)",   # Title part 2
  'PType' => 'thinbarline',
  'xmiss1' => 10,
  'xmiss2' => 12,
  'horizontal' => True, # Horizontal plot
  );
require 'missing.php';
