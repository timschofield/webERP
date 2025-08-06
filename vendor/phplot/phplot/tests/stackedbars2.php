<?php

# Test stacked bar plots - no shading
# Originally from the PHPlot Reference Manual, Example: Stacked Bars...
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'compat' => True,            # Backward-compatible image
  'shading' => 0,              # Bar shading depth, 0 for off, NULL to skip
  );
require 'stackedbars.php';
