<?php

# Testing phplot - Setting tick increment and/or number of ticks
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'numytick' => 4,         // Number of Y tick marks, NULL to not set
  'ymin' => 1,             // Min Y plot range for SetPlotAreaWorld
  );
require_once 'tickset.php';
