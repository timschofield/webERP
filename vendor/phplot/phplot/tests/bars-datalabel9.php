<?php

# PHPlot test: Bars with datalabel - 9
# This is a parameterized test. See the script named at the bottom for details.
# This has to override the data array, because we need large numbers.
$tp = array(
  'suffix' => " Y number formatting",       # Title part 2
  'ngroups' => 4,          # Number of bar groups
  'nbars' => 1,             # Number of bars per group
  'yprec' => 1,             # Y formatting precision (digits), NULL to omit
  'data' => array(array('=A=', 0), array('=B=', 123.45), array('=C=', 2000),
                  array('=D=', 3456.78)),
  );
require 'bars-datalabel.php';
