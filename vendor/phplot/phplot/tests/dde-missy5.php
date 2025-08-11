<?php

# Missing Y values in data-data-error plots: case 5
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\npoints, multiple, draw-broken",   # Title part 2
  'multiline' => True,     # True for 3 lines, false for 1 line.
  'plot-type' => 'points',   # lines, points, linepoints
  'draw-broken' => True,   # See SetDrawBrokenLines
  );
require 'dde-missy.php';
