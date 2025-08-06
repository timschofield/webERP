<?php

# Testing legend relative position - margin adjustment case 1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Legend CR with R margin adjustment off (-5,0)',
  'relto' => 'image',       # Relative to: image | plot | title | NULL to skip
  'lx' => 1, 'ly' => 0.5,   # Legend box reference point, relative coords
  'bx' => 1, 'by' => 0.5,   # Base point, relative coords
  'ox' => -5, 'oy' => 0,    # Additional pixel offset
  'callback' => 'setmarg',  # Callback, call before DrawGraph
  'ttfontsize' => 8,        # If not NULL, use TT font at this size
  'ttlinespace' => 12,      # If not NULL, set TT font line space factor
  );

# Callback for margin adjustment:
function setmarg($plot)
{
    list($legend_width, $legend_height) = $plot->GetLegendSize();
    // L, R, T, B
    $plot->SetMarginsPixels(NULL, $legend_width + 10, NULL, NULL);
}
require 'setlegrel.php';
