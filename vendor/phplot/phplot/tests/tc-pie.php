<?php

# PHPlot test: Truecolor pie chart without shading
# Parameters that can be set externally:
if (!isset($shading)) $shading = 0; // Shading, empty string to omit
if (!isset($alpha)) $alpha = 50;    // Default data colors alpha, NULL to skip
require_once 'phplot.php';
require_once 'phplot_truecolor.php';

$data = array();
for ($i = 0; $i < 16; $i++) $data[] = array('', 1);

$p = new Phplot\Phplot\phplot_truecolor(800, 800);
if ($shading === '') $d_shading = "default shading";
elseif ($shading === 0) $d_shading = "no shading";
else $d_shading = "shading=$shading";
$p->SetTitle("Truecolor Piechart with alpha=$alpha, $d_shading");
$p->SetDataType('text-data-single');
$p->SetDataValues($data);
if (isset($alpha)) $p->SetDataColors(NULL, NULL, $alpha);
$p->SetPlotType('pie');
if ($shading !== '') $p->SetShading($shading);
$p->DrawGraph();
