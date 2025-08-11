<?php

# PHPlot test: Output file - 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (png, via constructor)",   # Title part 2
  'ftype' => 'png',         # file type: png gif jpg, NULL to omit.
  'useset' => False,         # True to use SetOutputFile, False to use constructor
  );
require 'outfile.php';
