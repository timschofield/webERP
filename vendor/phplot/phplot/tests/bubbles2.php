<?php

# PHPlot test: Bubble plot - truecolor with translucent bubbles
require_once 'phplot.php';
require_once 'phplot_truecolor.php';

$n_x = 50; // Number of rows (X values)
$n_y = 6;   // Number of Y values per X. Also number of colors.
$max_x = 20; // X range
$max_y = 100; // Y range
$max_z = 100; // Z range
$alpha = 80;  // Alpha (transparency) for data colors. 0=opaque, 127=clear.

$data = array();
mt_srand(1);
for ($x = 0; $x < $n_x; $x++) {
    $row = array('', mt_rand(0, $max_x - 1));  // Label and X value
    for ($y = 0; $y < $n_y; $y++) {
        $row[] = mt_rand(0, $max_y-1); // Y value
        $row[] = mt_rand(0, $max_z-1); // Z value
    }
    $data[] = $row;
}
$legend = array();
for ($y = 0; $y < $n_y; $y++) $legend[] = "Data Set $y";

$p = new Phplot\Phplot\phplot_truecolor(800, 600);
$p->SetTitle("Bubble Plot - Random data, Truecolor\nTranslucent (alpha=$alpha) bubbles");
$p->SetDataColors(NULL, NULL, $alpha);
$p->SetDataType('data-data-xyz');
$p->SetPlotType('bubbles');
$p->SetDataValues($data);
$p->SetLegend($legend);
$p->SetPlotAreaWorld(0, 0,$max_x, $max_y);
$p->DrawGraph();
