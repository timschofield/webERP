<?php

# PHPlot Test - Ticks, Lengths and Labels - much longer
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ' - very long ticks',           # Title part 2
  'xticklen' => 80,     # X Tick length (outside graph)
  'yticklen' => 80,     # Y Tick length (outside graph)
  );
require 'tick.php';
