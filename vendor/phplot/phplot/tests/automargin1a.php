<?php

# Testing phplot - Automatic margin calculation - 1a
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ', 4 Titles, T/L ticks, B/R Labels',   # Title part 2
  'do_title' => True,           # True to display the main title
  'x_title_pos' => 'both',  # X Title Position: plotdown plotup both none
  'y_title_pos' => 'both',  # Y Title Position: plotleft plotright both none
  'xticklabel' => 'plotdown', # X Tick & label position: none|both|plotup|plotdown
  'xtick' => 'plotup',        # X Tick override, if different from xticklabel
  'yticklabel' => 'plotright', # Y Tick & label position: none|both|plotleft|plotright
  'ytick' => 'plotleft',        # Y Tick override, if different from yticklabel
  );
require 'automargin.php';
