<?php

# PHPlot test: Legend with line markers, case: 2,0
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => "Line Widths, No Shapes in Legend",   # Plot subtitle
  'plot_type' => 'linepoints',            # Plot type; must work with data-data
  'set_linewidths' => True,          # Vary line widths?
  'legend_use_shapes' => False,      # Use shapes vs colorboxes in legend?
  );
require 'legendlinemarker.php';
