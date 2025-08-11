<?php

# Testing phplot - "N" Lines with parameters - 4
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ' (16 lines, vary width and style)',  # Title part 2
  'nlines' =>  16,          # How many lines to draw (1-16)
  'LWidths' => array(2, 4, 6, 8, 10),        # SetLineWidths: integer or array or NULL
  'LStyles' => array('solid', 'solid', 'dashed'),    # SetLineStyles: solid|dashed or array or NULL
        );
require 'lines-n.php';
