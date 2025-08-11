<?php

# PHPlot test - Pie Chart Sizing and Label Variations - Unsupported data labels
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "Data type does not support pie labels",       # Title part 2
  'data_choice' => 3,          # Select data array: 1, 2 or 3
  'pie_label_args' => array('label'),
  'image_aspect' => 'L',       # Image aspect: S=square, P=portrait, L=landscape
  );

require 'piesize.php';
