<?php

# PHPlot test: "Stock market" plot, using error bars - 1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\n(bar shape: line, color: red)",   # Title part 2
  'EBShape' => 'line',      # ErrorBarShape: tee or line or NULL to omit
  'EBColors' => 'red',      # ErrorBarColors: color or arran or NULL to omit
  );
require 'stock.php';
