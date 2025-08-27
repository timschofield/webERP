<?php

# Title text tests - 5
# This is a parameterized test. See the script named at the bottom for details.
# Local:
$tp = array(
  'title' => 'Title Text (1 line, 4 titles)',  # First or only line
  'title_lines' => 1,       # Number of lines in the main, X, and Y titles
  'x_title_pos' => 'both',  # X Title Position: plotdown plotup both none
  'y_title_pos' => 'both',  # Y Title Position: plotleft plotright both none
  );
require 'title_text.php';
