<?php

# PHPlot test: Legend with line markers, case: 3,1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => "Use Shapes in Legend, Line Styles, half colorbox width.",   # Plot subtitle
  'plot_type' => 'squared',            # Plot type; must work with data-data
  'set_linewidths' => False,          # Vary line widths?
  'legend_use_shapes' => True,      # Use shapes vs colorboxes in legend?
  'plot_area_background' => True,   # Change plot area background color?
  'colorbox_width' => 0.5,          # If not NULL, colorbox width factor
  'set_styles' => True,             # Vary line styles?
  );
require 'legendlinemarker.php';
