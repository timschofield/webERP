<?php

# PHPlot test: Plot auto-range test with partial range specification
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Clip lower limit DataMin < PlotMin < DataMax',
  'plot_min_y' => 70,     # SetPlotAreaWorld Ymin
  'plot_max_y' => NULL,    # SetPlotAreaWorld Ymax
  );
require 'range-part.php';
