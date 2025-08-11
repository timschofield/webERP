<?php

# PHPlot test: Legend border control - Border on with color and background color
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Blue border with lavendar background',
  'border_color' => 'blue',         # Legend border color if set
  'legend_bg_color' => 'lavender',  # Legend background color
  );
require 'legendborder.php';
