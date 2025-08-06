<?php

# Partial margin specification with SetPlotAreaPixels - 4
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nMove right side to 600",           # Title part 2
  'doSetPlotAreaPixels' => True,   # Call SetSetPlotAreaPixels?
  'PlotAreaPixels' => array(NULL,NULL,600,NULL),  # Args for SetPlotAreaPixels
  );
require 'pmarg.php';
