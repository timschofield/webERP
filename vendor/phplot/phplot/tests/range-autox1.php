<?php

# PHPlot Test: for reference manual, tick increment calculation
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'subtitle' => "Ref. Man.: Advanced, Automatic Tick Increment Calculations\n"
              . "Example of decimal-mode tick increment choice",
  'min' => 0,              # Lowest data value
  'max' => 135,            # Highest data value
  'tick_mode' => 'decimal',  # Tick selection mode
  );
require 'range-auto.php';
