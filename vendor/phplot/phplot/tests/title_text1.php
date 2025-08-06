<?php

# Title text tests - 1
# This is a parameterized test. See the script named at the bottom for details.
# Local:
$tp = array(
  'title' => 'Title Text (3 lines, default spacing)',  # First or only line
  'title_lines' => 3,       # Number of lines in the main, X, and Y titles
  'line_spacing' => NULL,   # Line spacing, or NULL to omit
  );
require 'title_text.php';
