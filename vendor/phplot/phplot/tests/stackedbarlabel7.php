<?php

# Test: stackedbars with labels, TTF with angle and format
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nData Labels, TrueType @ 30deg and formatted", # Title part 2
  'ydatalabel' => 'plotstack',     # Y data label position, NULL to skip
  'edgedata' => True,      # If true, use some low vals (0s and 1s) in the data
  'custom' => 'customize', # Custom callback function name.
);
require_once 'config.php';  // For font info

# Additional customization
function customize($plot)
{
  global $phplot_test_ttfonts, $phplot_test_ttfdir; // From config.php
  $plot->SetFontTTF('y_label',
     $phplot_test_ttfdir .  $phplot_test_ttfonts['sansitalic'], 8);
  $plot->SetYDataLabelType('data', 2);
  $plot->SetYDataLabelAngle(30);
}

require 'stackedbars.php';
