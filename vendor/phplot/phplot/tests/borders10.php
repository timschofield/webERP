<?php

# Plot and image borders - case 10
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plotborder' => array('top', 'bottom', 'right'),     # Plot border type or NULL to skip
  'pbcolor' => 'blue',        # Grid color, used for plot border
  'imageborder' => 'raised',    # Image border type or NULL to skip
  'ibcolor' => 'red',        # Image border color
  'ibwidth' => 2,            # Image border width
  );
require 'borders.php';
