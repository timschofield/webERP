<?php

# PHPlot test: Legend text & background color, case 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'legend_bg_color' => 'plum',
  'legend_text_color' => 'red', # Set legend text color, overrides general
  );
require 'legendtxbgcolor.php';
