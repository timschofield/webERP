<?php

# Testing legend relative position - relative to title but no title.
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'title' => NULL,       # Special case, no title
  'suffix' => NULL,      # Special case, no title
  'relto' => 'title',    # Relative to: image | plot | title | NULL to skip
  'lx' => 0, 'ly' => 0,  # Legend box reference point, relative coords
  'bx' => 1, 'by' => 0,  # Base point, relative coords
  'ttfontsize' => 10,    # If not NULL, use TT font at this size
  );
require 'setlegrel.php';
