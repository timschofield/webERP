<?php

# PHPlot test: Truecolor Lines plot with GIF output
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'alpha' => 50,           # Alpha adjustment for data colors, NULL to skip
  'output' => 'gif',       # Output format: png | gif | jpg
  );
require 'tc-lines.php';
