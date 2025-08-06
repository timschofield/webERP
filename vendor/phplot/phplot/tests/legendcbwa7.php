<?php

# PHPlot test: Legend color box width adjust, case 7
$cbwa = 5;
$tp = array(
  'suffix' => ": Color Box Width Adjust\nText right, Colorbox right, Width Adjust 0.5, Font TTF12",
  'textalign' => 'right',
  'colorboxalign' => 'right',
  'cbwa' => 0.5,
  'use_ttf' => TRUE,
  'ttfsize' => 12,
  );
require 'legend_--.php';
