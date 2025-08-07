<?php

# PHPlot test: tick anchor points, case 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Base case 3',    # Sub-title
  'x_tick_anchor' => 0.5,     # Anchor for X ticks or NULL to not set one
  'y_tick_anchor' => 0.25,     # Anchor for Y ticks or NULL to not set one
  );
require 'tickanchor.php';
