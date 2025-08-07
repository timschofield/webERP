<?php

# PHPlot test: Plot auto-range test
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => 'Decimal tick step (2x): check edge, expecting step=100',
  'min' => 0,              # Lowest data value
  'max' => 999,            # Highest data value
  'adjust_mode' => 'T',    # Range adjust mode: 'T', 'R', or 'I'
  'adjust_amount' => 0,    #  % of range for adjustment
  'mintick' => 5,          # Minimum tick count
  'tick_mode' => 'decimal', # Tick selection mode
  );
require 'range-auto.php';
