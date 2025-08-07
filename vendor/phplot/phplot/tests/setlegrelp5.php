<?php

# Testing legend relative position - case plot-5
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Align CR to plot CR, TTF10 LS16',           # Title part 2
  'lx' => 1, 'ly' => 0.5,         # Legend box fixed point, relative coords
  'relto' => 'plot',                 # Relative to: 'image' or 'plot'
  'bx' => 1, 'by' => 0.5,               # Base point, relative coords
  'ttfontsize' => 10,  # If not NULL, use TT font at this size
  'ttlinespace' => 16, # If not NULL, set TT font line space factor
  );
require 'setlegrel.php';
