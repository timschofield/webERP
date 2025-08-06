<?php

# PHPlot Test - Ticks, Lengths and Labels - longer
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ' - len 20, cross 10',           # Title part 2
  'skiptick' => NULL,          # Skip ticks: NULL or string with BTRL
  'xticklen' => 20,     # X Tick length (outside graph)
  'yticklen' => 20,     # Y Tick length (outside graph)
  'xtickcross' => 10,   # X Tick crossing (inside)
  'ytickcross' => 10,   # Y Tick crossing (inside)
  );
require 'tick.php';
