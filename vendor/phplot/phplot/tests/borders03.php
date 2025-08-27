<?php

# Plot and image borders - case 03
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plotborder' => 'left',     # Plot border type or NULL to skip
  'pbcolor' => 'cyan',        # Grid color, used for plot border
  'ibcolor' => 'red',        # Image border color
  );
require 'borders.php';
