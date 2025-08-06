<?php

# PHPlot Test - Ticks, Lengths and Labels - Ticks/labels on all 4 sides
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ' - 4 sided labels',           # Title part 2
  'xticklabel' => 'both', # X Tick label position: none|both|plotup|plotdown
  'yticklabel' => 'both', # Y Tick label position: none|both|plotleft|plotright
  );
require 'tick.php';
