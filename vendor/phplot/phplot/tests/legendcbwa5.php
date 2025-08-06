<?php

# PHPlot test: Legend color box width adjust, case 5
$cbwa = 5;
$tp = array(
  'suffix' => ": Color Box Width Adjust\nText right, Colorbox left, Width Adjust 0.5, Font GD1",
  'textalign' => 'right',
  'colorboxalign' => 'left',
  'cbwa' => 0.5,
  'legendfont' => 1,
  );
require 'legend_--.php';
