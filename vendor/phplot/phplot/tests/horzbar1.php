<?php

# Horizontal bars - case 1, Reference: vertical bars
# This is a parameterized test. See the script named at the bottom for details.
$tp = array_merge(array(
  'suffix' => "\nReference - Vertical Bars",       # Title part 2
  'horizontal' => False,        # Horizontal or vertical bars
  ));
require 'horzbar.php';
