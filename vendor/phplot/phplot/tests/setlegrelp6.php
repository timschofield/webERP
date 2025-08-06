<?php

# Testing legend relative position - case plot-6, equiv to default
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Same as default: Align TR to plot TR off (-5,5)',   # Title part 2
  'lx' => 1, 'ly' => 0,         # Legend box fixed point, relative coords
  'relto' => 'plot',                 # Relative to: 'image' or 'plot'
  'bx' => 1, 'by' => 0,               # Base point, relative coords
  'ox' => -5, 'oy' => 5,
  );
require 'setlegrel.php';
