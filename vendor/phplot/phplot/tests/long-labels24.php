<?php

# X label size and angle test - TTF Angle 4
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ' TTF 10pt -90d',  # Title part 2
  'MaxLen' => 25,           # Max label length (int * 5)
  'angle' => -90,            # Label text angle, in degrees
  'TTF' => True,            # If True, use TrueType fonts, else GD fonts
  'FontSize' => 10,         # GD font size/TTF font point size, NULL to omit
  );
require 'long-labels.php';
