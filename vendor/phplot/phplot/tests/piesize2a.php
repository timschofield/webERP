<?php

# PHPlot test - Pie Chart Sizing and Label Variations - label scale position (a)
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "No labels (0)\nWidth limiting size",
  'shading' => 0,           # If set, pie shading SetShading()
  'label_pos' => 0,             # If set, label position SetLabelScalePosition()
  'plot_border' => 'full',       # If set, plot border type SetPlotBorderType()
  'image_aspect' => 'P',       # Image aspect: S=square, P=portrait, L=landscape
  );
require 'piesize.php';
