<?php

# PHPlot test: Legend with line markers, case: 0,4
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => "Line shape markers, wide OR dashed liness.",   # Plot subtitle
  'plot_type' => 'lines',            # Plot type; must work with data-data
  'set_linewidths' => True,          # Vary line widths?
  'legend_use_shapes' => True,       # Use shapes vs colorboxes in legend?
  'set_styles' => True,              # Vary line styles?
  );
require 'legendlinemarker.php';
