<?php

# Plot and image borders - case 09
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plotborder' => array('sides'),     # Plot border type or NULL to skip
  'pbcolor' => 'red',        # Grid color, used for plot border
  'imageborder' => 'raised',    # Image border type or NULL to skip
  'ibcolor' => 'red',        # Image border color
  'ibwidth' => 4,            # Image border width
  );
require 'borders.php';
