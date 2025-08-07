<?php
# Bar chart bar direction test - thinbarline case all Y>=0
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Thinbarline, Range starts at Y=0', # Title part 2
  'ymin' => 0,              # Min Y
  'ymax' => 10,             # Max Y
  'plottype' => 'thinbarline',
  );
require 'bardir.php';
