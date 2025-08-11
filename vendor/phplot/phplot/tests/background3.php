<?php

# Background image - 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (scaled clouds outside, bubbles within)",       # Title part 2
  'image1' => 'images/clouds.jpg', # Background image for entire graph, NULL for none
  'image2' => 'images/bubbles.png', # Background image for plot area, NULL for none
  'mode1' => 'scale',  # Graph background mode: centeredtile, tile, scale
  'mode2' => 'scale',  # Plot area background mode: centeredtile, tile, scale
  'pabgnd' => True, # If image2 is null, draw a plot area background?
  'truecolor' => True,  # Use Truecolor image due to JPEG background
  );
require 'background.php';
