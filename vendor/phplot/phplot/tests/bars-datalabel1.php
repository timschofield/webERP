<?php

# PHPlot test: Bars with datalabel - 1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " 4x2, unshaded",       # Title part 2
  'ngroups' => 4,           # Number of bar groups
  'nbars' => 2,             # Number of bars per group
  'fontsize' => NULL,       # Label font size or NULL to omit
  'shading' => 0,           # SetShading: 0 or pixels or NULL to omit
  );
require 'bars-datalabel.php';
