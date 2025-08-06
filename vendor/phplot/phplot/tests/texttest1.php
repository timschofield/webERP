<?php

# Text tests
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (GD font variations)", # Title part 2
  'use_ttf' => False,  # If true use TT fonts
  'use_gdf' => True,  # If true use GD fonts
  );
require 'texttest.php';
