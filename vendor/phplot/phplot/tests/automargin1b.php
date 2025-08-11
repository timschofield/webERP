<?php

# Testing phplot - Automatic margin calculation - 1b
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ', B/L Titles, T/R ticks & Labels',   # Title part 2
  'do_title' => True,           # True to display the main title
  'x_title_pos' => 'plotdown',  # X Title Position: plotdown plotup both none
  'y_title_pos' => 'plotleft',  # Y Title Position: plotleft plotright both none
  'xticklabel' => 'plotup', # X Tick & label position: none|both|plotup|plotdown
  'xtick' => NULL,            # X Tick override, if different from xticklabel
  'yticklabel' => 'plotright', # Y Tick & label position: none|both|plotleft|plotright
  'ytick' => NULL,              # Y Tick override, if different from yticklabel
  );
require 'automargin.php';
