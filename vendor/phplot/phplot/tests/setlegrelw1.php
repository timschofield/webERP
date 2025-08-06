<?php

# Testing legend relative position - case world-1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Align TL to world (1,8) off (5,-5)',           # Title part 2
  'lx' => 0, 'ly' => 0,         # Legend box fixed point, relative coords
  'relto' => 'world',                 # Relative to: 'image' or 'plot'
  'bx' => 1, 'by' => 8,               # Base point, world coords
  'ox' => 5, 'oy' => -5,  # Additional pixel offset
  );
require 'setlegrel.php';
