<?php

# Testing legend relative position - case plot-2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Align BR to plot BR, TTF14',           # Title part 2
  'lx' => 1, 'ly' => 1,         # Legend box fixed point, relative coords
  'relto' => 'plot',                 # Relative to: 'image' or 'plot'
  'bx' => 1, 'by' => 1,               # Base point, relative coords
  'ttfontsize' => 14,  # If not NULL, use TT font at this size
  );
require 'setlegrel.php';
