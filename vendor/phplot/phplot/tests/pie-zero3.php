<?php

# PHPlot test: Bug 1827263, spoiled chart if close to zero - case 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " shaded",   # Title part 2
  'case' => 3,              # Data test case
  );
require 'pie-zero.php';
