<?php

# Stacked Bars - horizontal, positive values
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nHorizontal, Positive values, Axis=0",  # Title part 2
  'horiz' => True,      # True for horizontal bars, False for vertical
  'signedness' => 1,   # 1:All >0, -1: All <0; 0: Both >0 and <0 data values
  'axis' => 0,
  );
require 'mixedsignstackedbar.php';
