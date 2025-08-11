<?php
# Testing legend relative position - margin adjustment case 2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Legend BC with B margin adjustment off (0,20)',
  'relto' => 'plot',       # Relative to: image | plot | title | NULL to skip
  'lx' => 0.5, 'ly' => 0,   # Legend box reference point, relative coords
  'bx' => 0.5, 'by' => 1,   # Base point, relative coords
  'ox' => 0, 'oy' => 20,     # Additional pixel offset
  'callback' => 'setmarg',  # Callback, call before DrawGraph
  );

# Callback for margin adjustment:
function setmarg($plot)
{
    list($legend_width, $legend_height) = $plot->GetLegendSize();
    // L, R, T, B
    $plot->SetMarginsPixels(NULL, NULL, NULL, $legend_height + 25);
}
require 'setlegrel.php';
