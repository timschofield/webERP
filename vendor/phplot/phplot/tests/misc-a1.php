<?php

# PHPlot test: Miscellaneous options: color map, line spacing, grid line style
# This is a parameterized test. See the script named at the bottom for details.

# Define 4 data color names for the 4 data sets:
$dcolors = array('data1', 'data2', 'data3', 'data4');

# Define a color map:
$graymap = array(
  'data1'   => array(0xcc, 0x66, 0xff),
  'data2'   => array(0x33, 0xcc, 0xff),
  'data3'   => array(0x66, 0xff, 0x99),
  'data4'   => array(0xff, 0x66, 0x33),
);

$tp = array(
  'suffix' => " (webcolors, spaced lines, solid grid)",       # Title part 2
  'colormap' => $graymap,    # Custom color map, NULL for none.
  'linespacing' => 8, # SetLineSpacing, or NULL to default
  'dashedgrid' => False,  # SetDrawDashedGrid, False for solid, True for dashed
  'datacolors' => $dcolors,  # Data Colors and Error Bar Colors maps, NULL for none.
  );
require 'misc-a.php';
