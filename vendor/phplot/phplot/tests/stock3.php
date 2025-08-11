<?php

# PHPlot test: "Stock market" plot, using error bars - 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\n(color: green, barsize: 20)",   # Title part 2
  'EBColors' => 'green',    # ErrorBarColors: color or arran or NULL to omit
  'EBSize' => 20,           # ErrorBarSize: integer pixels or NULL to omit
  );
require 'stock.php';
