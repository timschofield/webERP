<?php

# Missing Y values in data-data-error plots: case 6
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nline/points, single, no draw-broken",   # Title part 2
  'multiline' => False,     # True for 3 lines, false for 1 line.
  'plot-type' => 'linepoints',   # lines, points, linepoints
  'draw-broken' => False,   # See SetDrawBrokenLines
  );
require 'dde-missy.php';
