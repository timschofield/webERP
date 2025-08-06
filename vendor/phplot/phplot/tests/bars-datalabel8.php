<?php

# PHPlot test: Bars with datalabel - 8
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " 10x4 unshaded",       # Title part 2
  'ngroups' => 10,          # Number of bar groups
  'nbars' => 4,             # Number of bars per group
  'fontsize' => NULL,       # Label font size or NULL to omit
  'shading' => 0,           # SetShading: 0 or pixels or NULL to omit
  );
require 'bars-datalabel.php';
