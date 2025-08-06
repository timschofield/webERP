<?php

# PHPlot test: Plot auto-range test with partial range specification
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Wrong-side test: DataMin < DataMax < PlotMin',
  'plot_min_y' => 120,    # SetPlotAreaWorld Ymin
  'plot_max_y' => NULL,    # SetPlotAreaWorld Ymax
  );
require 'range-part.php';
