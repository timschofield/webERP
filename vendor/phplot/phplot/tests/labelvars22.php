<?php

# Testing phplot - tick/data label variant formatting - case 22
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'XT below @ 90, XD above at 0',    # Title line 2
  'x' => True,              # Chart type, explicit X values or not
  'xt_pos' => 'plotdown',   # X Tick Label position, NULL to skip
  'xd_pos' => 'plotup',     # X Data Label position, NULL to skip
  'x_angle' => 90,          # X Label angle, NULL to skip
  'xd_angle' => 0,          # X Data Label angle, NULL to skip
  );
require 'labelvars.php';
