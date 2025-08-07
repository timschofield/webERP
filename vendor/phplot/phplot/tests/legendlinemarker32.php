<?php

# PHPlot test: Legend with line markers, case: 3,0
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => "Use Shapes in Legend, default colorbox width.",   # Plot subtitle
  'plot_type' => 'squared',            # Plot type; must work with data-data
  'set_linewidths' => True,          # Vary line widths?
  'legend_use_shapes' => True,      # Use shapes vs colorboxes in legend?
  );
require 'legendlinemarker.php';
