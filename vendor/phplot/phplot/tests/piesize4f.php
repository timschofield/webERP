<?php

# PHPlot test - Pie Chart Sizing and Label Variations - Label Type (f)
# This is a parameterized test. See the script named at the bottom for details.

# Custom label format function:
function fmtlab($lab, $extra)
{
  return $extra . $lab . $extra;
}

$tp = array(
  'suffix' => 'Label Formatting',    # Title part 2
  'data_choice' => 1,          # Select data array: 1 or 2
  'pie_label_args' => array('index', 'custom', 'fmtlab', '==='),
  'ttfonts' => TRUE,           # Use TrueType fonts?
  'font_size' => 10,           # If set, TrueType or GD font size
  );
require 'piesize.php';
