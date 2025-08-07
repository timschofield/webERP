<?php

# PHPlot test: tick anchor points, case 5
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Case 5',    # Sub-title
  'start' => 100,              # Data min value
  'stop' => 1000,               # Data max value
  'delta' => 60,               # Data step value
  'x_tick_anchor' => 500,     # Anchor for X ticks or NULL to not set one
  'y_tick_anchor' => 200,     # Anchor for Y ticks or NULL to not set one
  'x_tick_step' => 200,       # Increment for X ticks or NULL to not set one
  'y_tick_step' => 250,       # Increment for Y ticks or NULL to not set one
  );
require 'tickanchor.php';
