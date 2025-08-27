<?php

# Missing Y values in data-data-error plots: case 4
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\npoints, single, no draw-broken",   # Title part 2
  'multiline' => False,     # True for 3 lines, false for 1 line.
  'plot-type' => 'points',   # lines, points, linepoints
  'draw-broken' => False,   # See SetDrawBrokenLines
  );
require 'dde-missy.php';
