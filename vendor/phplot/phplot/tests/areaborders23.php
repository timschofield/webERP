<?php

# PHPlot test: areas+borders - squaredarea, borders, colors, raised X axis
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plot_type' => 'squaredarea',
  'draw_borders' => TRUE,
  'border_colors' => TRUE,
  'x_axis' => 10,
  );
require 'areaborders00.php';
