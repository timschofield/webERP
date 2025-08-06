<?php

# Testing PHPlot: Y Data Label Lines, for horizontal plots - case 1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "1 points dataset, labels left, lines to left",    # Title part 2
  'groups' => 1,            # Number of data groups (1 or more)
  'labelpos' => 'plotleft',     # Y Data Label Position: plotleft, plotright, both, none
  'labellines' => True,    # Draw data lines? False or True
  'plottype' => 'points',   # Plot type: lines points linepoints
  );
require 'ydatalabellines.php';
