<?php

# PHPlot test - Pie Chart Sizing and Label Variations - Pie/image size (j)
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'pie_diam_factor ignored with unshaded pie',        # Title part 2
  'pie_diam_factor' => 2,      # If set, oblateness for shaded pie (dflt=0.5)
  'image_aspect' => 'L',       # Image aspect: S=square, P=portrait, L=landscape
  'shading' => 0,              # If set, pie shading SetShading()
  );
require 'piesize.php';
