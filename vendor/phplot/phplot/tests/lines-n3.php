<?php

# Testing phplot - "N" Lines with parameters - 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ' (7 lines, width 3, solid/dash)',  # Title part 2
  'nlines' =>  7,           # How many lines to draw (1-16)
  'LWidths' => 3,           # SetLineWidths: integer or array or NULL
  'LStyles' => array('solid', 'dashed'),     # SetLineStyles: solid|dashed or array or NULL
        );
require 'lines-n.php';
