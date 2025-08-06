<?php

# Testing legend relative position - case image-1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Align TR to image TR',           # Title part 2
  'lx' => 1, 'ly' => 0,         # Legend box fixed point, relative coords
  'relto' => 'image',                 # Relative to: 'image' or 'plot'
  'bx' => 1, 'by' => 0,               # Base point, relative coords
  );
require 'setlegrel.php';
