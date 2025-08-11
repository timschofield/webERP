<?php

# PHPlot test: Legend with line markers, case: 1,1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => "Shapes in Legend.",   # Plot subtitle
  'plot_type' => 'points',            # Plot type; must work with data-data
  'set_linewidths' => False,          # Vary line widths?
  'legend_use_shapes' => True,      # Use shapes vs colorboxes in legend?
  );
require 'legendlinemarker.php';
