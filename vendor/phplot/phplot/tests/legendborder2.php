<?php

# PHPlot test: Legend border control - No border
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'No legend border',
  'draw_border' => FALSE,           # Draw border? True False NULL (to skip)
  );
require 'legendborder.php';
