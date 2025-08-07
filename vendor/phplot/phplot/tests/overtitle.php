<?php

# Title overwrite with TTF text - bug report 3010116
# (Copied from manyplots.php test script)

# "When creating multiple plots in one image, the title (using TTF text)
# is drawn each time. This results in bad rendering of antialised TTF..."

# This can be set externally to determine when to set the title. (See the
# work-around in the bug report.) 0 means before the first. Use a large
# number to mean before the last.
if (empty($title_sequence))
  $title_sequence = 0;

require_once 'phplot.php';
require_once 'config.php';  // For Truetype fonts

$font = $phplot_test_ttfdir . $phplot_test_ttfonts['serif'];
$n_plots = 4;
$height_of_each_plot = 100;
$title_space = 50;
$space_below_plots = 60;

if ($title_sequence >= $n_plots)
    $title_sequence = $n_plots - 1; // Set title before last plot.

# Fake some data:
for ($i = 0; $i < $n_plots; $i++) {
  $report[$i] = array(array('Alpha', $i),
                      array('Beta', 1.5*$i),
                      array('Gamma', 2*$i));
}
$max_x = 2 * ($n_plots - 1) + 1;

$image_height = $n_plots * ($height_of_each_plot + $space_below_plots)
                + $title_space;

$plot = new Phplot\Phplot\phplot(800, $image_height);
$plot->SetFontTTF('title', $font, 14);
$plot->SetFontTTF('x_title', $font, 14);
$plot->SetFontTTF('y_title', $font, 10);

# Disable auto-output:
$plot->SetPrintImage(0);

$title = "Test $n_plots Plots with TTF Title (sequence $title_sequence)";

$y1 = $title_space;                    // Top of plot area
for($i = 0; $i < $n_plots; $i++) {
    if ($i == $title_sequence) $plot->SetTitle($title);
    $y2 = $y1 + $height_of_each_plot;      // Bottom of plot area
    # fwrite(STDERR, "Plot $i area: min=(80, $y1) : max=(740, $y2)\n");
    $plot->SetPlotAreaPixels(80, $y1, 740,$y2);
    $plot->SetDataType('text-data');
    $plot->SetDataValues($report[$i]);
    $plot->SetPlotAreaWorld(NULL, 0, NULL, $max_x);
    $plot->SetDataColors(array('blue'));
    $plot->SetXTickLabelPos('none');
    $plot->SetXDataLabelPos('plotdown');
    $plot->SetXTickPos('plotdown');
    $plot->SetYTickIncrement(1);
    $plot->SetXTitle("Chart $i X Values");
    $plot->SetYTitle("Chart $i Y Values");
    $plot->SetPlotType('bars');
    $plot->DrawGraph();
    $y1 = $y2 + $space_below_plots; // Start next plot below last plot
}
$plot->PrintImage();
