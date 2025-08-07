<?php

# Stacked Bars - vertical, negative values
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nVertical, Negative values",  # Title part 2
  'signedness' => -1,   # 1:All >0, -1: All <0; 0: Both >0 and <0 data values
  );
require 'mixedsignstackedbar.php';
