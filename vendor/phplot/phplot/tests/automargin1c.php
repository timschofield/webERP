<?php
# Testing phplot - Automatic margin calculation - 1c
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ', 1 Title, 4 sided ticks & Labels',   # Title part 2
  'do_title' => True,           # True to display the main title
  'x_title_pos' => 'none',  # X Title Position: plotdown plotup both none
  'y_title_pos' => 'none',  # Y Title Position: plotleft plotright both none
  'xticklabel' => 'both', # X Tick & label position: none|both|plotup|plotdown
  'xtick' => NULL,            # X Tick override, if different from xticklabel
  'yticklabel' => 'both', # Y Tick & label position: none|both|plotleft|plotright
  'ytick' => NULL,              # Y Tick override, if different from yticklabel
  );
require 'automargin.php';
