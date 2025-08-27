<?php

# PHPlot test - Pie Chart Sizing and Label Variations - plot border (b)
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Border=T/B/L',           # Title part 2
  'shading' => 0,           # If set, pie shading SetShading()
  'plot_border' => array('top', 'bottom', 'left'),    # If set, plot border type SetPlotBorderType()
  'plot_margins' => array(20, 40, 100, 10),   # If set, array SetMarginsPixels(l,r,t,b)
  );
require 'piesize.php';
