<?php

# PHPlot test: Legend with line markers, case: 2,2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => "No Line Widths, Line Styles, Use Shapes in Legend",   # Plot subtitle
  'plot_type' => 'linepoints',            # Plot type; must work with data-data
  'set_linewidths' => False,          # Vary line widths?
  'legend_use_shapes' => True,      # Use shapes vs colorboxes in legend?
  'set_styles' => True,               # Vary line styles?
  'more_legendlines' => 3,           # Additional legend lines after $ny
  );
require 'legendlinemarker.php';
