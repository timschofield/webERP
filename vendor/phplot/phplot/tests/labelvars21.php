<?php

# Testing phplot - tick/data label variant formatting - case 21
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'XT below @ 0, XD above @45d',    # Title line 2
  'x' => True,              # Chart type, explicit X values or not
  'xt_pos' => 'plotdown',   # X Tick Label position, NULL to skip
  'xd_pos' => 'plotup',     # X Data Label position, NULL to skip
  'x_angle' => NULL,        # X Label angle, NULL to skip
  'xd_angle' => 45,         # X Data Label angle, NULL to skip
  );
require 'labelvars.php';
