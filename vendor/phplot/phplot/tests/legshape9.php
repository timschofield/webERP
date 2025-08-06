<?php

# Legend shape marker tests - case 9
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'TTF 12pt spacing:6, colorbox align=none',  # Title line 2
  'useshapes' => True,     # True for shape markers, false for color boxes
  'fontsize' => 12,       # Use TT font at this size
  'linespacing' => 6,    # Line spacing scale
  'plottype' => 'linepoints',   # Plot type, points or linepoints
  'textalign' => 'right',      # Text alignment: left | right, NULL to ignore
                            #  both textalign and colorboxalign.
  'colorboxalign' => 'none',  # Color box alignment: left | right | none
  'colorboxwidth' => 3,  # Color box width horizontal scale adjust
  );
require 'legshape.php';
