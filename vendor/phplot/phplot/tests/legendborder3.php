<?php

# PHPlot test: Legend border control - No border, background color on
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'No border, with background color',
  'draw_border' => FALSE,           # Draw border? True False NULL (to skip)
  'legend_bg_color' => 'lavender',  # Legend background color
  );
require 'legendborder.php';
