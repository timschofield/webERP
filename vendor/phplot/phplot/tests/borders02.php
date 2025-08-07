<?php

# Plot and image borders - case 02
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plotborder' => 'sides',     # Plot border type or NULL to skip
  'pbcolor' => 'green',        # Grid color, used for plot border
  'imageborder' => 'none',    # Image border type or NULL to skip
  'ibcolor' => 'red',        # Image border color
  );
require 'borders.php';
