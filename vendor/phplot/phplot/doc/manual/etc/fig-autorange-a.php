<?php

# PHPlot Reference: generate figures showing automatic range adjustment
# This generates one of 3 figures (see $case below), each with 4 plots
# showing the cumulative steps for automatic range calculation.
require_once 'phplot.php';

# Case 'a' is default. Set to 'b' or 'c' in calling script.
if (!isset($case)) $case = 'a';
switch ($case) {
case 'a':
    $ymin = 25;       //  0  < DataMin < DataMax
    $ymax = 105;
    break;

case 'b':
    $ymin = -25;      //  DataMin < 0 < DataMax
    $ymax = 105;
    break;

case 'c':
    $ymin = -105;     //  DataMin < DataMax < 0
    $ymax = -25;
    break;
}

$plotlot_type = 'linepoints';
$data_type = 'data-data';
$n_points = 5;

# Build a data array spanning ymin to ymax exactly.
$data = array();
$y = $ymin;
$dy = ($ymax - $ymin) / ($n_points - 1);
for ($i = 1; $i <= $n_points; $i++) {
  if ($i == $n_points) $y = $ymax; // Avoid cumulative round-off error
  $data[] = array('', $i - 1, $y);
  $y += $dy;
}

# Base setup for all 4 sub-plots:
$plot = new Phplot\Phplot\phplot(800, 400);
$plot->SetTitle("PHPlot Auto-range Illustration\n"
              . "Case ($case) Y data range $ymin to $ymax");
$plot->SetDataType($data_type);
$plot->SetDataValues($data);
$plot->SetPlotType($plotlot_type);
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->SetXDataLabelPos('none');
$plot->SetPrintImage(False);
$plot->SetImageBorderType('solid');

# Sub-plot area limits for Y (in pixels):
$py1 = 55;
$py2 = 355;
# Pixel limits for X:
$pdx = 195; // Approx plot_width / 4 sub-plots
$px_l = 40; // Left offset
$px_r = 5;  // Right inset


# Sub-plot (1) shows the initial plot range = date range.
$plot->SetPlotAreaPixels($px_l, $py1, $pdx - $px_r, $py2);
$plot->SetXTitle('(1) Initial Range');
# Disable zero magnet and range adjustment:
$plot->TuneYAutoRange(0, 'R', 0);
$plot->DrawGraph();

# Sub-plot (2) shows the plot range after zero magnet.
$plot->SetPlotAreaPixels($pdx + $px_l, $py1, 2 * $pdx - $px_r, $py2);
$plot->SetXTitle('(2) Zero Magnet');
#  Reset to auto-range, auto-axis:
$plot->SetPlotAreaWorld(NULL, NULL, NULL, NULL);
$plot->SetXAxisPosition(NULL);
#  Set zero magnet on, leave range adjust off:
$plot->TuneYAutoRange(1, 'R', 0);
$plot->DrawGraph();

# Sub-plot (3) shows the plot range after end adjust / increase range.
$plot->SetPlotAreaPixels(2 * $pdx + $px_l, $py1, 3 * $pdx - $px_r, $py2);
$plot->SetXTitle('(3) End Adjustment');
#  Reset to auto-range, auto-axis:
$plot->SetPlotAreaWorld(NULL, NULL, NULL, NULL);
$plot->SetXAxisPosition(NULL);
#  Set zero magnet on, end adjust on, but leave at mode R - don't go to tick.
$plot->TuneYAutoRange(1, 'R', 0.03);
$plot->DrawGraph();

# Sub-plot (4) shows the plot range after end adjust / adjust to tick.
$plot->SetPlotAreaPixels(3 * $pdx + $px_l, $py1, 4 * $pdx - $px_r, $py2);
$plot->SetXTitle('(4) Adjust to Tick');
#  Reset to auto-range, auto-axis:
$plot->SetPlotAreaWorld(NULL, NULL, NULL, NULL);
$plot->SetXAxisPosition(NULL);
#  Set zero magnet on, restore default adjust mode (Ticks) and amount (3%).
$plot->TuneYAutoRange(1, 'T', 0.03);
$plot->DrawGraph();


# Final: image output
$plot->PrintImage();
