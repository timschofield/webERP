<?php

# Testing PHPlot: Y Data Label Lines, for horizontal plots - case 4
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "3 lines dataset, labels left, lines to left",    # Title part 2
  'groups' => 3,            # Number of data groups (1 or more)
  'labelpos' => 'plotleft',     # Y Data Label Position: plotleft, plotright, both, none
  'labellines' => True,    # Draw data lines? False or True
  'plottype' => 'lines',   # Plot type: lines points linepoints
  );
require 'ydatalabellines.php';
