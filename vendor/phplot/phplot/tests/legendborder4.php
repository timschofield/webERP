<?php

# PHPlot test: Legend border control - Grid color is default for legend border
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Grid color (red) is default for legend border',
  'draw_border' => TRUE,            # Draw border? True False NULL (to skip)
  'grid_color' => 'red',            # Grid color
  );
require 'legendborder.php';
