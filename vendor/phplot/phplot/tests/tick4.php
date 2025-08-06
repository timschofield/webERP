<?php

# PHPlot Test - Ticks, Lengths and Labels - skip ticks (2)
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ' - Skip top, right',           # Title part 2
  'skiptick' => 'TR',          # Skip ticks: NULL or string with BTRL
  );
require 'tick.php';
