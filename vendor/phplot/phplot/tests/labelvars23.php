<?php

# Testing phplot - tick/data label variant formatting - case 23
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'XT as #.# below, XD above upcased at 90d',    # Title line 2
  'x' => True,              # Chart type, explicit X values or not
  'xt_pos' => 'plotdown',   # X Tick Label position, NULL to skip
  'xd_pos' => 'plotup',     # X Data Label position, NULL to skip
  'xd_angle' => 90,         # X Data Label angle, NULL to skip
  'x_type' => 'data',       # X Labels: format type
  'x_type_arg' => 2,        # X Labels: format type argument
  'xd_type' => 'custom',    # X Data Labels: format type
  'xd_type_arg' => 'fmtlab',# X Data Labels: format type argument
  );

# Custom formatting for Y labels:
function fmtlab($s)
{
  return strtoupper($s);
}

require 'labelvars.php';
