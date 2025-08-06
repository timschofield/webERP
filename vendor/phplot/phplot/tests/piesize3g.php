<?php

# PHPlot test - Pie Chart Sizing and Label Variations - Pie/image size (g)
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Landscape',     # Title part 2
  'pie_diam_factor' => 2,      # If set, oblateness for shaded pie (dflt=0.5)
  'image_aspect' => 'L',       # Image aspect: S=square, P=portrait, L=landscape
  );
require 'piesize.php';
