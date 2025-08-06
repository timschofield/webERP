<?php

# PHPlot test: Bubble plot - master - simple plot
require_once 'phplot.php';

# Parameters which can be set in a calling script:
#   $subtitle
#   $bubbles_min_size, $bubbles_max_size
#   $data           Override data array
#   $plot_area      Array of 1-4 values for SetPlotAreaWorld
# These are only used if $data is not set:
#   $n_x            Number of X values (columns)
#   $n_y            Number of Y values per X

# Apply defaults:
if (empty($subtitle)) $subtitle = "Sequential, Alternating Z values";
if (empty($n_x)) $n_x = 20;  // Number of X values (columns)
if (empty($n_y)) $n_y = 6;   // Number of Y values per X
if (empty($data)) {
    $data = array();
    for ($x = 1; $x <= $n_x; $x++) {
        $row = array('', $x);
        for ($y = 1; $y <= $n_y; $y++) {
            $row[] = $y;
            $row[] = ($y & 1) ? $x : ($n_x + 1 - $x); // Z value
        }
        $data[] = $row;
    }
}

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle("Bubble Plot - " . $subtitle);
if (isset($bubbles_min_size)) $p->bubbles_min_size = $bubbles_min_size;
if (isset($bubbles_max_size)) $p->bubbles_max_size = $bubbles_max_size;
if (isset($plot_area))
  call_user_func_array(array($p, 'SetPlotAreaWorld'), $plot_area);
$p->SetDataType('data-data-xyz');
$p->SetPlotType('bubbles');
$p->SetDataValues($data);
$p->DrawGraph();
