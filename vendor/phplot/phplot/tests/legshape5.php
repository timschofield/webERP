<?php

# Legend shape marker tests - case 5
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Points plot, shape markers, varying shape sizes',  # Title line 2
  'useshapes' => True,     # True for shape markers, false for color boxes
  'plottype' => 'points',   # Plot type, points or linepoints
  'setpointsizes' => True, # True to vary the point shape sizes
  );
require 'legshape.php';
