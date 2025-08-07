<?php

# PHPlot test: Horizontal error plots with style controls
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plot_type' => 'lines',       # A plot type supporting error plots
  'horizontal' => True,         # True for horizontal plot, else vertical
  );
require 'horzerrplt.php';
