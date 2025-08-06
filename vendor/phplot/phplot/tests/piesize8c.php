<?php

# PHPlot test - Pie Chart Sizing and Label Variations - Custom multi-part label
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "Multi-field labels\n data type does not support pie labels",          # Title part 2
  'data_choice' => 3,          # Select data array: 1, 2 or 3
  'pie_label_args' => array(array('value', 'label'), 'custom', 'mylabels'),
  'image_aspect' => 'L',       # Image aspect: S=square, P=portrait, L=landscape
  );

# Custom label format:
function mylabels($label)
{
    // Extract label parameters. Use 3rd arg $limit to handle spaces in labels.
    list($value, $label) = explode(' ', $label, 2);
    // Format the label values
    return sprintf("Label='%s' Value:%.1f", $label, $value);
}

require 'piesize.php';
