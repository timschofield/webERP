<?php

# Testing phplot - Wrong ticks on X and Y - 1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'xmin' => 0,              # SetPlotAreaWorld: x min
  'xmax' => 2,              # SetPlotAreaWorld: x max
  'ymin' => 0,              # SetPlotAreaWorld: y min
  'ymax' => 4,              # SetPlotAreaWorld: y max
  'xti' => 0.1,              # XTickIncrement
  'yti' => 0.2,              # YTickIncrement
  );
require 'tickcount.php';
