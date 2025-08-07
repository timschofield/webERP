<?php

# Testing phplot - tick/data label variant formatting - case 05
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'YT both sides w/2 dec; YD default format',    # Title line 2
  'y_type' => 'data',     # Y labels: format type
  'y_type_arg' => 2,     # Y labels: format type argument
  'yd_type' => '',        # Y labels: format type
  'yt_pos' => 'both',      # Y Tick Label position, NULL to skip
  );
require 'labelvars.php';
