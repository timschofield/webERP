<?php

# Multi-line data labels, GD at 90 deg
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ', GD text @ 90 deg',           # Title part 2
  'font' => NULL,         # TrueType font file to use, or NULL for GD font
  'angle' => 90,          # Label text angle
  );
require 'multiline-label.php';
