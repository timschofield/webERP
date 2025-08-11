<?php

# PHPlot test: Truecolor Lines plot with controllable parameters

# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
# Note: Several parameters are adjusted if 'truecolor' is False.
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'alpha' => NULL,           # Alpha adjustment for data colors, NULL to skip
  'antialias' => False,      # If true, use anti-aliasing
  'noalphablend' => False,   # If true, disable alpha blending
  'gamma' => NULL,           # Gamma adjust, NULL to skip
  'truecolor' => True,       # Truecolor or Palette if false
  'output' => 'png',         # Output format: png | gif | jpg
  'savealpha' => False,      # Save separate alpha channel?
        ), $tp);
require_once 'phplot.php';
require_once 'phplot_truecolor.php';

function pre_plot($img)
{
    global $tp;
    if ($tp['antialias']) {
        if (!function_exists('imageantialias')) {
            echo "Skipping test: missing imageantialias function\n";
            exit(2);
        }
        imageantialias($img, True);
    }
    if ($tp['noalphablend']) imagealphablending($img, False);
}

# Post-drawing callback.
function post_plot($img)
{
    global $tp;
    if (isset($tp['gamma'])) imagegammacorrect($img, 1.0, $tp['gamma']);
    if ($tp['savealpha']) imagesavealpha($img, True);
}

# Option dependencies: most options require truecolor.
if (!$tp['truecolor']) {
    $tp['alpha'] = NULL;
    $tp['antialias'] = False;
    $tp['noalphablend'] = False;
    $tp['gamma'] = NULL;
}
# Save alpha requires alpha blending be turned off.
if ($tp['savealpha']) $tp['noalphablend'] = True;

# Build the Data array:
mt_srand(0);
$data = array();
for ($x = 0; $x < 16; $x++) {
    $row = array('', $x);
    for ($j = 0; $j < 10; $j++) {
      $row[] = mt_rand(0, $x + $j);
    }
    $data[] = $row;
}

# This gets imploded to be the title, after options are added on:
$title = array("Lines plot");

if ($tp['truecolor']) {
    $p = new Phplot\Phplot\phplot_truecolor(1024, 768);
    $title[] = "Truecolor";
} else {
    $p = new Phplot\Phplot\phplot(1024, 768);
    $title[] = "Palette Color";
}
$p->SetCallback('draw_setup', 'pre_plot');
$p->SetCallback('draw_all', 'post_plot');
# Reload data colors and apply alpha to all:
if (isset($tp['alpha'])) {
    $p->SetDataColors(NULL, NULL, $tp['alpha']);
    $title[] = "Alpha=" . $tp['alpha'];
}
$p->SetFileFormat($tp['output']);
$title[] = strtoupper($tp['output']) . ' Output';

if ($tp['antialias']) $title[] = "Antialiased";
if ($tp['noalphablend']) $title[] = "No alpha blending";
if (isset($tp['gamma'])) $title[] = "Gamma=" . $tp['gamma'];
if ($tp['savealpha']) $title[] = "Save alpha";
$p->SetTitle(implode(', ', $title));
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetLineWidths(3);
$p->SetLineStyles('solid');
$p->SetPlotType('lines');
$p->SetXTickIncrement(1);
$p->SetYTickIncrement(1);
$p->SetDrawDashedGrid(False); // Or it doesn't show with anti-aliasing
$p->SetDrawXGrid(True);
$p->DrawGraph();
