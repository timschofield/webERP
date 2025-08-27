<?php

# PHPlot test: Data Label Lines - case 13
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\n(3 errorplot datasets, labels above, lines up)",    # Title part 2
  'groups' => 3,            # Number of data groups (1 or more)
  'labelpos' => 'plotup',     # X Data Label Position: plotup, plotdown, both, none
  'labellines' => True,    # Draw data lines? False or True
  'plottype' => 'points', # Plot type
  'errorplot' => True,      # True for lines, points, or linepoints error plot
  );
require 'datalabellines.php';
