<?php

# PHPlot test: Legend with line markers, case: 2,1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => "No Line Widths, Use Shapes in Legend",   # Plot subtitle
  'plot_type' => 'linepoints',            # Plot type; must work with data-data
  'set_linewidths' => False,          # Vary line widths?
  'legend_use_shapes' => True,      # Use shapes vs colorboxes in legend?
  );
require 'legendlinemarker.php';
