<?php

# PHPlot test: Legend text & background color, case 2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'image_bg_color' => 'wheat',
  'plot_bg_color' => 'plum',
  'legend_bg_color' => 'salmon',
  'use_shapes' => True,
  'image_bg_color' => 'plum',    # Image background color
  'plot_bg_color' => 'salmon',     # Plot area background color
  'legend_bg_color' => 'wheat',   # Legend background (requires SetLegendBgColor)
  'use_shapes' => True,       # True to use shape markers in legend
  'text_color' => 'red',        # Set general text color
  'legend_text_color' => 'blue', # Set legend text color, overrides general
  );
require 'legendtxbgcolor.php';
