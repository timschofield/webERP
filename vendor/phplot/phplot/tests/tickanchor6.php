<?php

# PHPlot test: tick anchor points, case 6
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Case 6',    # Sub-title
  'start' => 0,              # Data min value
  'stop' => 100,               # Data max value
  'delta' => 10,               # Data step value
  'x_tick_anchor' => 225,     # Anchor for X ticks or NULL to not set one
  'y_tick_anchor' => -10,     # Anchor for Y ticks or NULL to not set one
  );
require 'tickanchor.php';
