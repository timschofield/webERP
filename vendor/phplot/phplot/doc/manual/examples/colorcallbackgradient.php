<?php
# PHPlot Example: Creative use of data colors
require_once __DIR__ . '/../../../src/phplot.php';
require_once __DIR__ . '/../../../src/phplot_truecolor.php';

# Callback for picking a data color.
# PHPlot will call this every time it needs a data color.
# This simply returns the row number as the color index.
function getcolor($img, $unused, $row, $col)
{
  return $row; // Use row, rather than column, as color index.
}

# Make some pseudo-random data.
mt_srand(1);
$data = array();
$value = 10;
for ($i = 0; $i < 500; $i++) {
  $data[] = array('', $i, $value);
  $value = max(0, $value + mt_rand(-9, 10));
}

# Make a color gradient array of blue:
$colors = array();
for ($b = 32; $b <= 255; $b += 2) $colors[] = array(0, 0, $b);
for ($b = 255; $b >= 32; $b -= 2) $colors[] = array(0, 0, $b);

# Use a truecolor plot image in order to get more colors.
$plot = new Phplot\Phplot\phplot_truecolor(800, 600);
$plot->SetImageBorderType('plain'); // Improves presentation in the manual

$plot->SetPlotType('thinbarline');
$plot->SetDataType('data-data');
$plot->SetDataValues($data);
$plot->SetLineWidths(2);
$plot->SetDataColors($colors);
$plot->SetXTickPos('none');
$plot->SetPlotAreaWorld(0, 0, 500, NULL);
$plot->SetTitle('Meaningless Data with Color Gradient');

# Establish the function 'getcolor' as a data color selection callback.
$plot->SetCallback('data_color', 'getcolor');

$plot->DrawGraph();
