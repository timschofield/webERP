<?php

# PHPlot test: Date/time range on X axis
#            H  M  S  mo da yr
$t1 = mktime(0, 0, 0, 8, 1, 2010);
$t2 = mktime(0, 0, 0, 8, 31, 2010);
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'xmin' => $t1,               # Lowest X data value
  'xmax' => $t2,               # Highest X data value
  'mintick' => 30,             # X minimum tick count
  'tickanchor' => $t1,         # XTick anchor, if set
  );
require 'range-datex.php';
