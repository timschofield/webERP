<?php

# Legend shape marker tests - case 6
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Shape markers, 8pt TTF text',  # Title line 2
  'useshapes' => True,     # True for shape markers, false for color boxes
  'fontsize' => 8,       # Use TT font at this size
  'plottype' => 'linepoints',   # Plot type, points or linepoints
  );
require 'legshape.php';
