<?php

# PHPlot test: Bug 1827263, spoiled chart if close to zero - case 2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " unshaded, labels inside",   # Title part 2
  'case' => 2,              # Data test case
  'shading' => 0,        # Pie shading, 0 for no shading, NULL for default (5)
  'labelpos' => 0.3,     # Pie label offset, NULL for none, < .5 for inside
  );
require 'pie-zero.php';
