<?php

# PHPlot test: Error test for SetRGBARray()
# Note: This tests error detection that was first added to PHPlot-6.0.0.
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'title' => "Error test - Bad color map",
  'suffix' => "",
  'colormap' => 'invalid',
  );
require 'misc-a.php';
