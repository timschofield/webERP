<?php

# PHPlot test: Data Label Lines - 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (1 dataset, labels up/down, lines through)",    # Title part 2
  'groups' => 1,            # Number of data groups (1 or more)
  'labelpos' => 'both',     # X Data Label Position: plotup, plotdown, both, none
  'labellines' => True,    # Draw data lines? False or True
  );
require 'datalabellines.php';
