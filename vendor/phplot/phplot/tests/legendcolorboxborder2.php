<?php

# PHPlot test: Legend Colorbox Borders, case 2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'colorboxborders' => 'databordercolor',  # NULL or arg to SetLegendColorboxBorders
  'colorboxwidth' => 4,  # NULL, 1 for default width, etc
  'setdbcolors' => TRUE,   # True to change Data Border colors
  'settextcolor' => TRUE,  # True to change Text color
  );
require 'legendcolorboxborder.php';
