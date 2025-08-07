<?php

# Testing phplot - "N" Lines with parameters - 2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ' (10 lines, solid)',  # Title part 2
  'nlines' =>  10,          # How many lines to draw (1-16)
  'LWidths' => NULL,        # SetLineWidths: integer or array or NULL
  'LStyles' => 'solid',     # SetLineStyles: solid|dashed or array or NULL
        );
require 'lines-n.php';
