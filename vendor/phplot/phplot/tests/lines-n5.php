<?php

# Testing phplot - "N" Lines with parameters - 5
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ' (4 thick custom dashed lines)',  # Title part 2
  'nlines' =>  4,          # How many lines to draw (1-16)
  'LWidths' => 4,          # SetLineWidths: integer or array or NULL
  'LStyles' => 'dashed',   # SetLineStyles: solid|dashed or array or NULL
  'DStyle' => '10-10', # SetDefaultDashedStyle: string or NULL
        );
require 'lines-n.php';
