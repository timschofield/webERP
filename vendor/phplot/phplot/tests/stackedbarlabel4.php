<?php

# Test: stackedbars with labels, no shading, moved X axis
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " - unshaded\nData Labels, raised X axis", # Title part 2
  'shading' => 0,        # Bar shading depth, 0 for off, NULL to skip
  'xaxispos' => 10,      # X axis position, default 0, NULL to skip.
  'ydatalabel' => 'plotstack',     # Y data label position, NULL to skip
  'edgedata' => True,      # If true, use some low vals (0s and 1s) in the data
);
require 'stackedbars.php';
