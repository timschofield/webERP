<?php

# PHPlot test: Horizontal error plots with style controls
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Set error bar shape=line, width=4, colors=cyan+magenta',
  'plot_type' => 'linepoints',  # A plot type supporting error plots
  'horizontal' => True,         # True for horizontal plot, else vertical
  'eb_shape' => 'line',         # Error bar shape if set: line | tee
  'eb_lwidth' => 4,          # Error bar line width in pixels if set
  'eb_colors' => array('cyan', 'magenta'),
  );
require 'horzerrplt.php';
