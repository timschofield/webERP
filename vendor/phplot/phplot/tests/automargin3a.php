<?php

# Testing phplot - Automatic margin calculation - 3a long ticks, visible
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ', Long visible ticks',   # Title part 2
  'do_title' => True,           # True to display the main title
  'x_title_pos' => 'both',  # X Title Position: plotdown plotup both none
  'y_title_pos' => 'both',  # Y Title Position: plotleft plotright both none
  'xticklabel' => 'both', # X Tick & label position: none|both|plotup|plotdown
  'xtick' => 'both',        # X Tick override, if different from xticklabel
  'yticklabel' => 'both', # Y Tick & label position: none|both|plotleft|plotright
  'ytick' => 'both',        # Y Tick override, if different from yticklabel
  'xticklen' => 20,     # X Tick length (outside graph)
  'yticklen' => 25,     # Y Tick length (outside graph)
  );
require 'automargin.php';
