<?php

# PHPlot test: areas+borders - stackedsquaredarea, borders, color, raised X
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plot_type' => 'stackedsquaredarea',
  'draw_borders' => TRUE,
  'border_colors' => TRUE,
  'x_axis' => 10,
  );
require 'areaborders00.php';
