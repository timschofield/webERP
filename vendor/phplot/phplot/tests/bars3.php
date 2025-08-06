<?php

# PHPlot test: Bar charts - 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (unshaded, red borders)",   # Title part 2
  'Shade' => 0,             # Shading: 0 for none or pixels or NULL to omit
  'DBColors' => 'red',      # DataBorderColors: color or array or NULL
  );
require 'bars.php';
