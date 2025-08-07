<?php

# Horizontal bars - case 8 options
# This is a parameterized test. See the script named at the bottom for details.
$tp = array_merge(array(
  'suffix' => "\nUnshaded, all <0, y axis and labels right, force xmin to -20",  # Title part 2
  'shade' => 0,                 # Bar shading, NULL to skip
  'negative' => 1,              # Make every nth data value negative
  'yaxis0' => True,             # Move Y axis to 0 if true
  'plotarea' => array(-20,NULL,NULL,NULL), # Array[4] for SetPlotAreaWorld, or NULL.
  'ydatalabelpos' => 'plotright',  # Y data label position (SetYDataLabelPos)
  ));
require 'horzbar.php';
