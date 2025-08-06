<?php

# PHPlot test: Bars with datalabel - 7
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " 4x4, deep shading",       # Title part 2
  'ngroups' => 4,           # Number of bar groups
  'nbars' => 4,             # Number of bars per group
  'fontsize' => 4,          # Label font size or NULL to omit
  'shading' => 10,          # SetShading: 0 or pixels or NULL to omit
  );
require 'bars-datalabel.php';
