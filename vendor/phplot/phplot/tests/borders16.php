<?php

# Plot and image borders - case 16
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plotborder' => 'full',     # Plot border type or NULL to skip
  'pbcolor' => 'blue',        # Grid color, used for plot border
  'imageborder' => 'solid',    # Image border type or NULL to skip
  'ibcolor' => 'aquamarine1',        # Image border color
  'ibwidth' => 32            # Image border width
  );
require 'borders.php';
