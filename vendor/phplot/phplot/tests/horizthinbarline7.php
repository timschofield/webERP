<?php

# PHPlot test - thinbarline, horiz & vert - horiz <0, axis@0
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'horiz' => True,          # Vertical or horizontal
  'low' => -100,            # Bottom of data range
  'high' => -10,            # Top of data range
  'axis0' => True,          # If true, move indep var axis to 0
  );
require 'horizthinbarline.php';
