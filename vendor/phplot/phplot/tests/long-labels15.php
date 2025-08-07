<?php

# X label size and angle test - TTF 5
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ' TTF 18pt serif-bold-italic',  # Title part 2
  'MaxLen' => 25,           # Max label length (int * 5)
  'TTF' => True,            # If True, use TrueType fonts, else GD fonts
  'FontSize' => 18,         # GD font size/TTF font point size, NULL to omit
  'FontName' => 'serifbolditalic',     # Font index name - see config.php
  );
require 'long-labels.php';
