<?php

# PHPlot test: Bubble plot - custom data color callback,
require_once 'phplot.php';

$n_p = 200; // Number of points
$max_x = 20; // X range
$max_y = 100; // Y range
$max_z = 100; // Z range
$warn_level = 60; // Green below this level
// Yellow between these levels
$danger_level = 90; // Red above this level

# Data color selection. $data is the passthru argument.
# Index into $data to find the Z value and make the color
# depend on Z. Low = green (color 0), middle = yellow (color 1),
# or high = red (color 2).
function getcolor($img, $data, $row, $col)
{
  global $warn_level, $danger_level;

  // Fetch the Z value from the array with rows
  // that look like:  (label, X, Yi, Zi, Yi, Zi...)
  // $col is 0 for the first Y,Z pair, 1 for the second, etc.
  $z = $data[$row][2 * $col + 3];
  if ($z < $warn_level) return 0; // Green
  if ($z < $danger_level) return 1; // Yellow
  return 2; // Red
}

$data = array();
mt_srand(1);
for ($i = 0; $i < $n_p; $i++) {
    $data[] = array('', mt_rand(0, $max_x-1),
        mt_rand(0, $max_y-1), mt_rand(0, $max_z-1));
}

$p = new Phplot\Phplot\phplot(1024, 768);
$p->SetTitle("Bubble Plot - Random Scatterplot with Custom Data Colors");
$p->SetCallback('data_color', 'getcolor', $data);
$p->SetDataType('data-data-xyz');
$p->SetDataColors(array('green', 'yellow', 'red'));
$p->SetPlotType('bubbles');
$p->SetPlotAreaWorld(0, 0, $max_x, $max_y);
$p->SetDataValues($data);
$p->DrawGraph();
