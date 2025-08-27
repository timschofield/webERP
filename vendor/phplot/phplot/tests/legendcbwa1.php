<?php

# PHPlot test: Legend color box width adjust, case 1
$cbwa = 5;
$tp = array(
  'suffix' => ": Color Box Width Adjust\nText left, Colorbox left, Width Adjust 0.5, Font default",
  'textalign' => 'left',
  'colorboxalign' => 'left',
  'cbwa' => 0.5,
  );
require 'legend_--.php';
