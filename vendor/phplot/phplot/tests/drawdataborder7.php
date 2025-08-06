<?php

# PHPlot test - SetDrawDrawBorders (PHPlot >= 6.0) - case 7
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plottype' => 'stackedbars',     # Plot type: bars or stackedbars
  'shading' => 0,        # Shaded: NULL for default shading, 0 unshaded, etc.
  'draw_borders' => False,   # SetDrawDataBorders: NULL to not call, True, False
  );
require 'drawdataborder.php';
