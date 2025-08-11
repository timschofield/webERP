<?php

# Plot and image borders - case 11
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plotborder' => array('full', 'right'),     # Plot border type or NULL to skip
  'imageborder' => 'raised',    # Image border type or NULL to skip
  'ibcolor' => 'red',        # Image border color
  'ibwidth' => 1,            # Image border width
  );
require 'borders.php';
