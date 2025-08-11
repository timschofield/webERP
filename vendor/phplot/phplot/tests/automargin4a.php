<?php

# Testing phplot - Automatic margin calculation - 4a axis
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ', Axis labels, positive data',   # Title part 2
  'x_title_pos' => 'both',  # X Title Position: plotdown plotup both none
  'y_title_pos' => 'both',  # Y Title Position: plotleft plotright both none
  'xticklabel' => 'xaxis', # X tick & label position: none|both|plotup|plotdown|xaxis
  'xtick' => NULL,    # X Tick override, if different from xticklabel
  'yticklabel' => 'yaxis', # Y Tick & label position: none|both|plotleft|plotright
  'ytick' => NULL,          # Y tick override, if different from yticklabel
  'xticklen' => 10,     # X Tick length (outside graph)
  'yticklen' => 10,     # Y Tick length (outside graph)
  'xdatalabel' => NULL, # X data label position: NULL|plotdown|plotup|both
  'xlt0' => False,        # True to have some negative (x < 0) X data
  'ylt0' => False,        # True to have some negative (y < 0) Y data
  );
require 'automargin.php';
