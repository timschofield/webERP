<?php

# PHPlot test: Legend with line markers, case: 1,2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => "Point shape markers, background color, 4x colorbox width.",   # Plot subtitle
  'plot_type' => 'points',            # Plot type; must work with data-data
  'set_linewidths' => False,          # Vary line widths?
  'legend_use_shapes' => True,      # Use shapes vs colorboxes in legend?
  'plot_area_background' => True,   # Change plot area background color?
  'colorbox_width' => 4,          # If not NULL, colorbox width factor
  );
require 'legendlinemarker.php';
