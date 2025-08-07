<?php
# PHPlot test - Pie Chart Sizing and Label Variations - Custom multi-part label
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Multi-field labels',          # Title part 2
  'data_choice' => 1,          # Select data array: 1, 2 or 3
  'pie_label_args' => array(array('index', 'value', 'percent', 'label'),
                            'custom', 'mylabels'),
  'image_aspect' => 'L',       # Image aspect: S=square, P=portrait, L=landscape
  );

# Custom label format:
function mylabels($label)
{
    // Extract label parameters. Use 3rd arg $limit to handle spaces in labels.
    list($index, $value, $percent, $label) = explode(' ', $label, 4);
    // Format the label values
    return sprintf("#%d '%s' (%.1f = %.1f%%)", $index, $label, $value, $percent);
}

require 'piesize.php';
