<?php

# Background image - 4
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (text stamp tiled under plot only)",       # Title part 2
  'image1' => NULL, # Background image for entire graph, NULL for none
  'image2' => 'images/watermark.png', # Background image for plot area, NULL for none
  'mode1' => 'scale',  # Graph background mode: centeredtile, tile, scale
  'mode2' => 'tile',  # Plot area background mode: centeredtile, tile, scale
  'pabgnd' => True, # If image2 is null, draw a plot area background?
  );
require 'background.php';
