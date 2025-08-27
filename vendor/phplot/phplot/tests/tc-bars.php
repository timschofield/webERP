<?php

# PHPlot test: Truecolor bars/stacked bars plot, alpha and/or shading
# Parameters that can be set externally:
if (!isset($shading)) $shading = 0; // Shading, empty string to omit
if (!isset($alpha)) $alpha = 50;    // Default data colors alpha, NULL to skip
if (!isset($plottype)) $plottype = 'bars'; // bars or stackedbars

require_once 'phplot.php';
require_once 'phplot_truecolor.php';
$data = array(
  array('Spring', 10, 20, 40, 45, 60),
  array('Summer', 15, 22, 40, 55, 80),
  array('Fall',   20, 24, 47, 65, 83),
  array('Winter', 20, 24, 47, 65, 83),
);
$p = new Phplot\Phplot\phplot_truecolor(800, 800);
if ($shading === '') $d_shading = "default shading";
elseif ($shading === 0) $d_shading = "no shading";
else $d_shading = "shading=$shading";

$p->SetTitle("Truecolor $plottype chart with alpha=$alpha, $d_shading");
$p->SetDataType('text-data');
$p->SetDataValues($data);
if (isset($alpha)) $p->SetDataColors(NULL, NULL, $alpha);
$p->SetPlotType($plottype);
if ($shading !== '') $p->SetShading($shading);
$p->SetXTickPos('none');
$p->DrawGraph();
