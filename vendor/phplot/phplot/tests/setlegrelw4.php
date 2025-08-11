<?php
# Testing legend relative position - case world-4
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Align CC to world (1,8), TTF16 LS=8',           # Title part 2
  'lx' => 0.5, 'ly' => 0.5,         # Legend box fixed point, relative coords
  'relto' => 'world',                 # Relative to: 'image' or 'plot'
  'bx' => 1, 'by' => 8,               # Base point, world coords
  'ttfontsize' => 16,  # If not NULL, use TT font at this size
  'ttlinespace' => 8, # If not NULL, set TT font line space factor
  );
require 'setlegrel.php';
