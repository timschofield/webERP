<?php

# PHPlot test: Data Label Lines - 7
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (3 lines, labels below, lines down)",    # Title part 2
  'groups' => 3,            # Number of data groups (1 or more)
  'labelpos' => 'plotdown',     # X Data Label Position: plotup, plotdown, both, none
  'labellines' => True,    # Draw data lines? False or True
  'plottype' => 'lines',   # Plot type: lines, points, or linepoints.
  );
require 'datalabellines.php';
