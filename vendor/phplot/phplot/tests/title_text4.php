<?php

# Title text tests - 4
# This is a parameterized test. See the script named at the bottom for details.
# Local:
$tp = array(
  'title' => 'Title Text (3 lines, spacing 8 after)',  # First or only line
  'title_lines' => 3,       # Number of lines in the main, X, and Y titles
  'line_spacing' => 8,   # Line spacing, or NULL to omit
  'line_spacing_before' => False,   # If true, set line_spacing before titles, else after.
  );
require 'title_text.php';
