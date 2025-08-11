<?php

# PHPlot test: Legend with line markers, case: 4,0
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => "Plot type does not support Shapes in Legend.",   # Plot subtitle
  'nx' => 50,                        # Number of X values
  'ny' => 1,                        # Number of datasets, or Y values per X
  'plot_type' => 'thinbarline',            # Plot type; must work with data-data
  'set_linewidths' => False,          # Vary line widths?
  'legend_use_shapes' => True,      # Use shapes vs colorboxes in legend?
  );
require 'legendlinemarker.php';
