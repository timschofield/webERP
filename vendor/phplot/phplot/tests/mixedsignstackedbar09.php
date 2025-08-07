<?php

# Stacked Bars - horizontal, negative values, without moving axis
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nHorizontal, Negative values, no axis move",  # Title part 2
  'horiz' => true,      # True for horizontal bars, False for vertical
  'signedness' => -1,   # 1:All >0, -1: All <0; 0: Both >0 and <0 data values
  );
require 'mixedsignstackedbar.php';
