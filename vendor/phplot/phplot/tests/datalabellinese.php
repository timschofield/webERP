<?php

# PHPlot test: Data Label Lines - case 14
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\n(2 errorplot datasets, labels up/down, lines through)",    # Title part 2
  'groups' => 2,            # Number of data groups (1 or more)
  'labelpos' => 'both',     # X Data Label Position: plotup, plotdown, both, none
  'labellines' => True,    # Draw data lines? False or True
  'plottype' => 'linepoints', # Plot type
  'errorplot' => True,      # True for lines, points, or linepoints error plot
  );
require 'datalabellines.php';
