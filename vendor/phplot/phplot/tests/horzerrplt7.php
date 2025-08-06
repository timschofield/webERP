<?php

# PHPlot test: Horizontal error plots with style controls
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Set error bar size=2, lwidth=3, color=black',
  'plot_type' => 'points',      # A plot type supporting error plots
  'horizontal' => True,         # True for horizontal plot, else vertical
  'eb_size' => 2,               # Error bar size in pixels if set
  'eb_lwidth' => 3,             # Error bar line width in pixels if set
  'eb_colors' => 'black',       # Error bar colors if set (array or single)
  );
require 'horzerrplt.php';
