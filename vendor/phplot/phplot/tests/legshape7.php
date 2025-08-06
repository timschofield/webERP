<?php

# Legend shape marker tests - case 7
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'text left, shape markers right width x4',  # Title line 2
  'useshapes' => True,     # True for shape markers, false for color boxes
  'plottype' => 'linepoints',   # Plot type, points or linepoints
  'textalign' => 'left',      # Text alignment: left | right, NULL to ignore
                            #  both textalign and colorboxalign.
  'colorboxalign' => 'right',  # Color box alignment: left | right | none
  'colorboxwidth' => 4,  # Color box width horizontal scale adjust
  );
require 'legshape.php';
