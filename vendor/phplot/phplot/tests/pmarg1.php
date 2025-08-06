<?php
# Partial margin specification with SetMarginsPixels - 1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nIncrease left & right margins",           # Title part 2
  'doSetMarginsPixels' => True,   # Call SetMarginsPixels?
  'MarginsPixels' => array(100,100,NULL,NULL),  # Args for SetMarginsPixels
  );
require 'pmarg.php';
