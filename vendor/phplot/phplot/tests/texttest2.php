<?php

# Text tests
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (TrueType font variations)", # Title part 2
  'use_ttf' => True,  # If true use TT fonts
  'use_gdf' => False,  # If true use GD fonts
  );
require 'texttest.php';
