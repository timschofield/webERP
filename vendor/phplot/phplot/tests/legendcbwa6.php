<?php

# PHPlot test: Legend color box width adjust, case 6
$cbwa = 5;
$tp = array(
  'suffix' => ": Color Box Width Adjust\nText right, Colorbox left, Width Adjust 3.0, Font GD5",
  'textalign' => 'right',
  'colorboxalign' => 'left',
  'cbwa' => 3.0,
  'legendfont' => 5,
  );
require 'legend_--.php';
