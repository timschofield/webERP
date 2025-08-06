<?php

# PHPlot test: Output file - 1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (gif, SetOutputFile)",   # Title part 2
  'ftype' => 'gif',         # file type: png gif jpg, NULL to omit.
  'useset' => True,         # True to use SetOutputFile, False to use constructor
  );
require 'outfile.php';
