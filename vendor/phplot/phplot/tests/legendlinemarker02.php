<?php

# PHPlot test: Legend with line markers, case: 0,2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => "Line shape markers, background color, 2x colorbox width.",   # Plot subtitle
  'plot_type' => 'lines',            # Plot type; must work with data-data
  'set_linewidths' => True,          # Vary line widths?
  'legend_use_shapes' => True,       # Use shapes vs colorboxes in legend?
  'plot_area_background' => True,   # Change plot area background color?
  'colorbox_width' => 2,          # If not NULL, colorbox width factor
  );
require 'legendlinemarker.php';
