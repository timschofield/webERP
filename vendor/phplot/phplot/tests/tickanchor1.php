<?php

# PHPlot test: tick anchor points, case 1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Base case 1',    # Sub-title
  'x_tick_anchor' => 0,     # Anchor for X ticks or NULL to not set one
  'y_tick_anchor' => 0,     # Anchor for Y ticks or NULL to not set one
  );
require 'tickanchor.php';
