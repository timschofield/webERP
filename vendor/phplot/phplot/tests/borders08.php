<?php
# Plot and image borders - case 08
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plotborder' => 'right',     # Plot border type or NULL to skip
  'pbcolor' => 'blue',        # Grid color, used for plot border
  'imageborder' => 'plain',    # Image border type or NULL to skip
  'ibcolor' => 'green',        # Image border color
  'ibwidth' => 4,            # Image border width
  );
require 'borders.php';
