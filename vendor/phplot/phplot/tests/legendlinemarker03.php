<?php

# PHPlot test: Legend with line markers, case: 0,3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => "Line Styles, Use Shapes in Legend, default colorbox width.",   # Plot subtitle
  'plot_type' => 'lines',            # Plot type; must work with data-data
  'set_linewidths' => False,         # Vary line widths?
  'legend_use_shapes' => True,       # Use shapes vs colorboxes in legend?
  'colorbox_width' => NULL,          # If not NULL, colorbox width factor
  'set_styles' => True,              # Vary line styles?
  'more_legendlines' => 2,           # Additional legend lines after $ny
  );
require 'legendlinemarker.php';
