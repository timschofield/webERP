<?php

# PHPlot test: Legend Colorbox Borders, case 1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'colorboxborders' => 'none',  # NULL or arg to SetLegendColorboxBorders
  'colorboxwidth' => NULL,  # NULL, 1 for default width, etc
  'setdbcolors' => FALSE,   # True to change Data Border colors
  'settextcolor' => FALSE,  # True to change Text color
  );
require 'legendcolorboxborder.php';
