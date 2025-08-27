<?php

# Testing legend relative position - case image-4
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Align TL to image TL, off (5,5)',           # Title part 2
  'lx' => 0, 'ly' => 0,  # Legend box fixed point, relative coords
  'relto' => 'image',    # Relative to: 'image' or 'plot'
  'bx' => 0, 'by' => 0,  # Base point, relative coords
  'ox' => 5, 'oy' => 5,  # Additional pixel offset
  );
require 'setlegrel.php';
