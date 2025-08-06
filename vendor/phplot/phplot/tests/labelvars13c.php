<?php

# Testing phplot - tick/data label variant formatting - case 13c
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Case 4: XT=down, XD=up',    # Title line 2
  'x' => True,              # Chart type, explicit X values or not
  'xt_pos' => 'plotdown',       # X Tick Label position, NULL to skip
  'xd_pos' => 'plotup',       # X Data Label position, NULL to skip
  );
require 'labelvars.php';
