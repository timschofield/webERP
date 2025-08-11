<?php

# PHPlot test: Log/log scales
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'c' => 100,        # Plotting XY = C
  't' => 10,         # If set, use this for X and Y tick steps, else auto
  'ar' => FALSE,     # Auto-range: if false, use SetPlotAreaWorld
  );
require 'range-log.php';
