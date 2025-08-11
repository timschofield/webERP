<?php

# PHPlot test: Horizontal error plots with style controls
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Set error bar width=2, colors=green+blue',
  'plot_type' => 'lines',       # A plot type supporting error plots
  'horizontal' => True,         # True for horizontal plot, else vertical
  'eb_lwidth' => 2,             # Error bar line width in pixels if set
  'eb_colors' => array('green', 'SkyBlue'), # reversed vs default
  );
require 'horzerrplt.php';
