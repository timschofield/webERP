<?php

# PHPlot test: Plots with missing data - plot type stackedbars
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (stackedbars)",   # Title part 2
  'PType' => 'stackedbars',
  'xmiss1' => 4,
  'xmiss2' => 13,
  );
require 'missing.php';
