<?php

# Test stacked bar plots - deep shading
$tp = array(
  'compat' => True,            # Backward-compatible image
  'shading' => 20,             # Bar shading depth, 0 for off, NULL to skip
  );
require 'stackedbars.php';
