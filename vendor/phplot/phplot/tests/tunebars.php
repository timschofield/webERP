<?php

# PHPlot test: Bar chart tuning variables - baseline shaded bars
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'plot_type' => 'bars',        # Plot type: bars or stackedbars
  'subtitle' => 'baseline',     # Subtitle
  'shading' => TRUE,            # True for default shading, False for unshaded
  'bar_extra_space' => NULL,    # tunable variable, NULL to not set it
  'group_frac_width' => NULL,   # tunable variable, NULL to not set it
  'bar_width_adjust' => NULL,   # tunable variable, NULL to not set it
        ), $tp);
require_once 'phplot.php';

$data = array(
  array('Jan', 10, 99,  0), array('Feb', 20, 97, 50), array('Mar', 30, 95, 50),
  array('Apr', 40, 90, 50), array('May', 50, 80, 50), array('Jun', 60, 70, 50),
  array('Jul', 70, 60, 50), array('Aug', 80, 50, 50), array('Sep', 90, 40, 50),
  array('Oct', 95, 30, 50), array('Nov', 97, 20, 50), array('Dec', 99, 10, 100),
);

extract($tp);
$title = "Tuning - " . ($shading? "Shaded" : "Unshaded")
       . " $plot_type - $subtitle\n";

$plot = new Phplot\Phplot\phplot(800, 600);

$plot->SetPlotType($plot_type);
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');

if (!$shading) $plot->SetShading(0);
if (isset($bar_extra_space)) {
    $plot->bar_extra_space = $bar_extra_space;
    $title .= "bar_extra_space=$bar_extra_space,";
} else {
    $title .= "bar_extra_space=(default),";
}
if (isset($group_frac_width)) {
    $plot->group_frac_width = $group_frac_width;
    $title .= " group_frac_width=$group_frac_width,";
} else {
    $title .= " group_frac_width=(default),";
}
if (isset($bar_width_adjust)) {
    $plot->bar_width_adjust = $bar_width_adjust;
    $title .= " bar_width_adjust=$bar_width_adjust";
} else {
    $title .=  "bar_width_adjust=(default)";
}
$plot->SetTitle($title);

$plot->DrawGraph();
