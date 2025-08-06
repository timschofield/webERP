<?php

# PHPlot test: tick anchor points, case 2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Base case 2',    # Sub-title
  'x_tick_anchor' => 0,     # Anchor for X ticks or NULL to not set one
  'y_tick_anchor' => 0,     # Anchor for Y ticks or NULL to not set one
  'x_tick_step' => 2,       # Increment for X ticks or NULL to not set one
  'y_tick_step' => 5,       # Increment for Y ticks or NULL to not set one
  );
require 'tickanchor.php';
