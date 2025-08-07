<?php

# Testing phplot - tick/data label variant formatting - case 14
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Case 5: XT=none, XD=n/a',    # Title line 2
  'x' => True,              # Chart type, explicit X values or not
  'xt_pos' => 'none',       # X Tick Label position, NULL to skip
  'xd_pos' => NULL,         # X Data Label position, NULL to skip
  );
require 'labelvars.php';
