<?php

# Testing phplot - Setting tick increment and/or number of ticks
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'ytickinc' => 1,         // Y tick increment, NULL to not set
  'numytick' => 3,         // Number of Y tick marks, NULL to not set
  'set_num_ticks_first' => FALSE,   // Set number of ticks before increment
  );
require_once 'tickset.php';
