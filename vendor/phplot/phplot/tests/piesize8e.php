<?php

# PHPlot test - Pie Chart Sizing and Label Variations - External data labels
# This is a parameterized test. See the script named at the bottom for details.

# Data color array, also used for labels. Must have 9 entries, matching the
# number of sectors defined by data_choice=3.
$colors = array('red', 'green', 'blue', 'cyan', 'yellow', 'magenta',
  'navy', 'lavender', 'gray',);

$tp = array(
  'suffix' => "Data labels in external array",       # Title part 2
  'data_choice' => 3,          # Select data array: 1, 2 or 3
  'pie_label_args' => array('index', 'custom', 'mylabels'),
  'image_aspect' => 'L',       # Image aspect: S=square, P=portrait, L=landscape
  'data_colors' => $colors,       # If set, data colors
  );

# Custom label format:
function mylabels($index)
{
    global $colors;
    return $colors[$index];
}

require 'piesize.php';
