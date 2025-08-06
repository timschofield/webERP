<?php

# Stacked Bars - horizontal, bipolar, unshaded, axis > 0
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nHorizontal, Positive/Negative values, axis=2",  # Title part 2
  'horiz' => true,     # True for horizontal bars, False for vertical
  'shading' => 0,    # Bar shading in pixels, 0 for flat, NULL for default
  'signedness' => 0,    # 1:All >0, -1: All <0; 0: Both >0 and <0 data values
  'axis' => 2,       # Move axis (X or Y) to value, NULL to skip
  );
require 'mixedsignstackedbar.php';
