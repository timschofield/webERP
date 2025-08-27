<?php

# PHPlot test: Horizontal error plots with style controls
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Set error bar size=10, color=red',
  'plot_type' => 'lines',       # A plot type supporting error plots
  'horizontal' => True,         # True for horizontal plot, else vertical
  'eb_size' => 10,              # Error bar size in pixels if set
  'eb_colors' => 'red',         # Error bar colors if set (array or single)
  );
require 'horzerrplt.php';
