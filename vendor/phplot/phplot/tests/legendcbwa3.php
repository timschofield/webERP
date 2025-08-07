<?php

# PHPlot test: Legend color box width adjust, case 3
$cbwa = 5;
$tp = array(
  'suffix' => ": Color Box Width Adjust\nText left, Colorbox right, Width Adjust 0.5, Font GD5",
  'textalign' => 'left',
  'colorboxalign' => 'right',
  'cbwa' => 0.5,
  'legendfont' => 5,
  );
require 'legend_--.php';
