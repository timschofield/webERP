<?php

# Testing legend relative position - case image-2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Align BR to image BR, off (-20,-5)',           # Title part 2
  'lx' => 1, 'ly' => 1,    # Legend box fixed point, relative coords
  'relto' => 'image',      # Relative to: 'image' or 'plot'
  'bx' => 1, 'by' => 1,    # Base point, relative coords
  'ox' => -20, 'oy' => -5,  # Additional pixel offset
  );
require 'setlegrel.php';
