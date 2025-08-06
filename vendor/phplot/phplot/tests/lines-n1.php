<?php
# Testing phplot - "N" Lines with parameters - 1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ' (4 lines, solid, 4 widths)',  # Title part 2
  'nlines' =>  4,           # How many lines to draw (1-16)
  'LWidths' => array(2, 4, 6, 8),        # SetLineWidths: integer or array or NULL
  'LStyles' => 'solid',     # SetLineStyles: solid|dashed or array or NULL
        );
require 'lines-n.php';
