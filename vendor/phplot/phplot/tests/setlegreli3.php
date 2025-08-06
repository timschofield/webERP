<?php

# Testing legend relative position - case image-3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Align BL to image BL, TTF10',           # Title part 2
  'lx' => 0, 'ly' => 1,         # Legend box fixed point, relative coords
  'relto' => 'image',                 # Relative to: 'image' or 'plot'
  'bx' => 0, 'by' => 1,               # Base point, relative coords
  'ttfontsize' => 10,  # If not NULL, use TT font at this size
  );
require 'setlegrel.php';
