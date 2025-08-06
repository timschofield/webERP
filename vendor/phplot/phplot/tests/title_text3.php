<?php

# Title text tests - 3
# This is a parameterized test. See the script named at the bottom for details.
# Local:
$tp = array(
  'title' => 'Title Text (3 lines, spacing 12 before)',  # First or only line
  'title_lines' => 3,       # Number of lines in the main, X, and Y titles
  'line_spacing' => 12,   # Line spacing, or NULL to omit
  'line_spacing_before' => True,   # If true, set line_spacing before titles, else after.
  );
require 'title_text.php';
