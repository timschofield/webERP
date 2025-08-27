<?php

# Plot and image borders - case 06
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plotborder' => 'top',     # Plot border type or NULL to skip
  'imageborder' => 'raised',    # Image border type or NULL to skip
  'ibcolor' => 'green',        # Image border color
  );
require 'borders.php';
