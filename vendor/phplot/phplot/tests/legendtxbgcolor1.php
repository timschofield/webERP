<?php

# PHPlot test: Legend text & background color, case 1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'image_bg_color' => 'wheat',
  'plot_bg_color' => 'plum',
  'legend_bg_color' => 'salmon',
  'text_color' => 'blue',        # Set general text color
  );
require 'legendtxbgcolor.php';
