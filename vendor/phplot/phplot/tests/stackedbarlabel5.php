<?php
# Test: stackedbars with labels, moved X axis, set YMIN
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nData Labels, raised X axis, move bottom range up", # Title part 2
  'xaxispos' => 10,      # X axis position, default 0, NULL to skip.
  'ydatalabel' => 'plotstack',     # Y data label position, NULL to skip
  'edgedata' => True,      # If true, use some low vals (0s and 1s) in the data
  'custom' => 'customize', # Custom callback function name.
);

# Additional customization
function customize($plot)
{
  $plot->SetPlotAreaWorld(NULL, 10, NULL, NULL);
}

require 'stackedbars.php';
