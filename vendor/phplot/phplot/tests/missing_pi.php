<?php

# PHPlot test: Plots with missing data - plot type pie
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (pie)",   # Title part 2
  'PType' => 'pie',
  'xmiss1' => 2,
  );
require 'missing.php';
