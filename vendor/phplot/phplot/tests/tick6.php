<?php

# PHPlot Test - Ticks, Lengths and Labels - 4 sided labels with 4 skips
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ' - 4 sided, skip 4',           # Title part 2
  'skiptick' => 'BTRL',          # Skip ticks: NULL or string with BTRL
  'xticklabel' => 'both', # X Tick label position: none|both|plotup|plotdown
  'yticklabel' => 'both', # Y Tick label position: none|both|plotleft|plotright
  );
require 'tick.php';
