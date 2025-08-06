<?php

# PHPlot test - Pie Chart Sizing and Label Variations - label scale position (b)
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "No labels (false)\nHeight limiting size",
  'shading' => 0,           # If set, pie shading SetShading()
  'label_pos' => False,             # If set, label position SetLabelScalePosition()
  'plot_border' => 'full',       # If set, plot border type SetPlotBorderType()
  'image_aspect' => 'L',       # Image aspect: S=square, P=portrait, L=landscape
  );
require 'piesize.php';
