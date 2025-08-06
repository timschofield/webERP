<?php

# PHPlot Test - Ticks, Lengths and Labels - data labels
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ' - data labels',           # Title part 2
  'xdatalabel' => 'plotdown', # X Data label position: none|both|plotup|plotdown
  'xticklabel' => 'none', # X Tick label position: none|both|plotup|plotdown
  'xtick' => 'plotdown',        # X Tick override, if different from xticklabel
  );
require 'tick.php';
