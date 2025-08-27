<?php

# PHPlot test: Data Label Lines - case 12
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\n(1 errorplot dataset, labels below, lines down)",    # Title part 2
  'groups' => 1,            # Number of data groups (1 or more)
  'labelpos' => 'plotdown',     # X Data Label Position: plotup, plotdown, both, none
  'labellines' => True,    # Draw data lines? False or True
  'plottype' => 'lines', # Plot type
  'errorplot' => True,      # True for lines, points, or linepoints error plot
  );
require 'datalabellines.php';
