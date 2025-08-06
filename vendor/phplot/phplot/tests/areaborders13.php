<?php

# PHPlot test: areas+borders - stackedarea, borders, colors, raised X
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plot_type' => 'stackedarea',
  'draw_borders' => TRUE,
  'border_colors' => TRUE,
  'x_axis' => 10,
  );
require 'areaborders00.php';
