<?php

# PHPlot test: Legend with line markers, case: 1,0
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => "No Shapes in Legend.",   # Plot subtitle
  'plot_type' => 'points',            # Plot type; must work with data-data
  'set_linewidths' => False,          # Vary line widths?
  'legend_use_shapes' => False,      # Use shapes vs colorboxes in legend?
  );
require 'legendlinemarker.php';
