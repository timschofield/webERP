<?php

# Test: stackedbars with labels, deep shading
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " - deep shading\nData Labels", # Title part 2
  'shading' => 20,        # Bar shading depth, 0 for off, NULL to skip
  'ydatalabel' => 'plotstack',     # Y data label position, NULL to skip
  'edgedata' => True,      # If true, use some low vals (0s and 1s) in the data
);
require 'stackedbars.php';
