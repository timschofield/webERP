<?php

# Partial margin specification with SetPlotAreaPixels - 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nMove upper left corner to 200,200",           # Title part 2
  'doSetPlotAreaPixels' => True,   # Call SetSetPlotAreaPixels?
  'PlotAreaPixels' => array(200,200,NULL,NULL),  # Args for SetPlotAreaPixels
  );
require 'pmarg.php';
