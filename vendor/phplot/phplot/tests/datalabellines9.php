<?php

# PHPlot test: Data Label Lines - case 9
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (2 datasets, labels below, lines down)",    # Title part 2
  'groups' => 2,            # Number of data groups (1 or more)
  'labelpos' => 'plotdown',     # X Data Label Position: plotup, plotdown, both, none
  'labellines' => True,    # Draw data lines? False or True
  'plottype' => 'bubbles', # Plot type
  );
require 'datalabellines.php';
