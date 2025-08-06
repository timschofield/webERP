<?php

# PHPlot test: Plot auto-range test with partial range specification
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Set upper limit DataMin < DataMax < PlotMax',
  'plot_min_y' => NULL,   # SetPlotAreaWorld Ymin
  'plot_max_y' => 200,     # SetPlotAreaWorld Ymax
  );
require 'range-part.php';
