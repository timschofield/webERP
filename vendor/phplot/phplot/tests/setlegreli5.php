<?php

# Testing legend relative position - case image-5
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Align CC to image CC, TTF8/LS=16',           # Title part 2
  'lx' => 0.5, 'ly' => 0.5,         # Legend box fixed point, relative coords
  'relto' => 'image',                 # Relative to: 'image' or 'plot'
  'bx' => 0.5, 'by' => 0.5,               # Base point, relative coords
  'ttfontsize' => 8,  # If not NULL, use TT font at this size
  'ttlinespace' => 16, # If not NULL, set TT font line space factor
  );
require 'setlegrel.php';
