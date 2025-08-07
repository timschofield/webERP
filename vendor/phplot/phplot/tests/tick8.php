<?php

# PHPlot Test - Ticks, Lengths and Labels - data labels, 2 sides
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ' - 2 sided data labels',           # Title part 2
  'xdatalabel' => 'both', # X Data label position: none|both|plotup|plotdown
  'xticklabel' => 'none', # X Tick label position: none|both|plotup|plotdown
  'xtick' => 'both',        # X Tick override, if different from xticklabel
  );
require 'tick.php';
