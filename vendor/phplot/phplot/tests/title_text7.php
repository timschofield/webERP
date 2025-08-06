<?php

# Title text tests - 7
# This is a parameterized test. See the script named at the bottom for details.
# Local:
$tp = array(
  'title' => 'Title Text (TTF, 4 lines, 4 titles)',  # First or only line
  'use_ttf' => True,        # True to use TTF text, False for GD text
  'title_lines' => 4,       # Number of lines in the main, X, and Y titles
  'x_title_pos' => 'both',  # X Title Position: plotdown plotup both none
  'y_title_pos' => 'both',  # Y Title Position: plotleft plotright both none
  );
require 'title_text.php';
