<?php

# Bar chart bar direction test - thinbarline case all Y<0
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Thinbarline, Range is all Y<0', # Title part 2
  'ymin' => -10,              # Min Y
  'ymax' => -1,             # Max Y
  'plottype' => 'thinbarline',
  );
require 'bardir.php';
