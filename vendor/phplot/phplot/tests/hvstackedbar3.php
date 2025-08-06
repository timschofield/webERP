<?php

# Horizontal Stacked Bars with Data Value Labels - unshaded, large vertical text
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'horiz' => True,      # True for horizontal bars, False for vertical
  'shading' => 0,       # Bar shading in pixels, 0 for flat, NULL for default
  'textangle' => 90,    # Label text angle, or NULL for default
  'textheight' => 14,   # Label text height in points, or NULL for default
  );
require 'hvstackedbar.php';
