<?php

# Testing legend relative position - margin adjustment case 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Legend TL (top aligned with plot) with L margin adjustment off (-5,0)',
  'relto' => 'plot',       # Relative to: image | plot | title | NULL to skip
  'lx' => 1, 'ly' => 0,    # Legend box reference point, relative coords
  'bx' => 0, 'by' => 0,    # Base point, relative coords
  'ox' => -5, 'oy' => 0,    # Additional pixel offset
  'callback' => 'setmarg',  # Callback, call before DrawGraph
  'ttfontsize' => 14,        # If not NULL, use TT font at this size
  );

# Callback for margin adjustment:
function setmarg($plot)
{
    list($legend_width, $legend_height) = $plot->GetLegendSize();
    // L, R, T, B
    $plot->SetMarginsPixels($legend_width + 10, NULL, NULL, NULL);
}
require 'setlegrel.php';
