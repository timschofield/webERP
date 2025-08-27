<?php

# Horizontal and Vertical Stacked Bars with Data Value Labels - baseline
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'horiz' => False,     # True for horizontal bars, False for vertical
  'shading' => NULL,    # Bar shading in pixels, 0 for flat, NULL for default
  'textangle' => NULL,  # Label text angle, or NULL for default
  'textheight' => NULL, # Label text height in points, or NULL for default
  'format' => NULL,     # Label format mode: NULL, 1 (LabelType) 2, (DataLabelType)
  'formattype' => 'data', # Label format type: NULL, 'data', 'printf'
  'formatarg' => 2,       # Label format type argument
        ), $tp);
require_once 'phplot.php';
require 'config.php'; # For TTF

extract($tp);  # Bring all config variables into local context

$data = array(
    array('A', 100, 200, 300, 400, 500, 600, 700, 800, 900),
    array('B',  10,  20,  30,  40,  50,  60,  70,  80,  90),
    array('C',  80,  90, 100, 110, 120, 130, 140, 150, 160),
    array('D', 900, 800, 700, 600, 500, 400, 300, 200, 100),
    array('E',  10,  20,  40,  80, 160, 320,  10,  15,  20),
);

# For method names:
$dep_var = $horiz ? 'X' : 'Y';
$ind_var = $horiz ? 'Y' : 'X';

$title = ($horiz ? 'Horizontal' : 'Vertical') . ' Stacked Bars';
# Build a subtitle describing the options
$subtitle = array();
if (isset($textheight)) $subtitle[] = "Text {$textheight}pts";
if (isset($textangle))  $subtitle[] = "Text {$textangle}deg";
if (isset($shading)) {
  if ($shading == 0) $subtitle[] = "Flat bars";
  else $subtitle[] = "Shaded ($shading) bars";
}
if (isset($format)) {
  if ($format == 1) $mode = '';           // E.g. SetXLabelType()
  elseif ($format == 2) $mode = 'Data';   // E.g. SetXDataLabelType()
  $subtitle[] = "Format (Set{$mode}LabelType) '$formattype, $formatarg'";
}

if (empty($subtitle))
    $title .= "\nDefaults (baseline)";
else
    $title .= "\n" . implode(", ", $subtitle);

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetDefaultTTFont($phplot_test_ttfdir . $phplot_test_ttfonts['sans']);
$plot->SetPlotType('stackedbars');
$plot->SetDataType($horiz ? 'text-data-yx' : 'text-data');
$plot->SetDataValues($data);
$plot->SetTitle($title);
if (isset($textheight))
    $plot->SetFont($horiz ? 'x_label' : 'y_label', NULL, $textheight);
if (isset($textangle))
    call_user_func(array($plot, "Set{$dep_var}DataLabelAngle"), $textangle);
if (isset($shading))
    $plot->SetShading($shading);
call_user_func(array($plot, "Set{$ind_var}TickPos"), 'none');
call_user_func(array($plot, "Set{$dep_var}DataLabelPos"), 'plotstack');

if (isset($format))
    call_user_func(array($plot, "Set{$dep_var}{$mode}LabelType"),
                   $formattype, $formatarg);

$plot->DrawGraph();
