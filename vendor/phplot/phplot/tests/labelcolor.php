<?php

# PHPlot test: label (tick, axis data, data value) color variations - baseline
# For post-5.6.0 with new controls to separately change colors for these.
# This is a parameterized test. Other scripts can set $colors and then include
# this script. See below.
require_once 'phplot.php';

# $colors array keys are elements: text ticklabel datalabel datavaluelabel
#  Values are false (or the key is unset) to not call that function,
#  true to call that function. The actual colors are coded here, chosen
#  for contrast.
# Example:
#    $colors = array('text' => True,
#                    'ticklabel' => True,
#                    'datalabel' => True,
#                    'datavaluelabel' => True,
#              );

# Optional settings:
if (empty($plot_type)) $plot_type = 'bars'; // bars | stackedbars
if (empty($data_type)) $data_type = 'text-data'; // text-data | text-data-yz

# Driver array: key => method, and color:
$cfg = array(
  'text' =>
     array('method' => 'SetTextColor', 'color' => 'red'),
  'ticklabel' =>
     array('method' => 'SetTickLabelColor', 'color' => 'DarkGreen'),
  'datalabel' =>
     array('method' => 'SetDataLabelColor', 'color' => 'purple'),
  'datavaluelabel' =>
     array('method' => 'SetDataValueLabelColor', 'color' => 'peru'),
);


$data = array(
  array('A', 1, 2, 3, 4),
  array('B', 2, 3, 4, 5),
  array('C', 3, 4, 5, 6),
);
$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetDataType($data_type);
$plot->SetDataValues($data);
$plot->SetPlotType($plot_type);

$plot->SetXTitle('X Axis Title Here');
$plot->SetYTitle('Y Axis Title Here');
// Change all 3 title colors to show the change in data value
// labels, which used title color incorrectly through 5.6.0
$plot->SetTitleColor('grey');
$plot->SetXTitleColor('SlateBlue');
$plot->SetYTitleColor('gold');

$plot->SetFont('x_label', '5');
$plot->SetFont('y_label', '5');
$plot->SetFont('x_title', '5');
$plot->SetFont('y_title', '5');
$plot->SetLegend(array('A', 'B', 'C'));
# Turn off ticks on independent axis, and turn on data value labels. This
# depends on the plot type and data type (horizontal or vertical):
$label_pos = $plot_type == 'stackedbars' ? 'plotstack' : 'plotin';
if ($data_type == 'text-data-yx') { // Horizontal plot
    $plot->SetYTickPos('none');
    $plot->SetXDataLabelPos($label_pos);
} else { // Vertical plot
    $plot->SetXTickPos('none');
    $plot->SetYDataLabelPos($label_pos);
}

# Starting value for title:
$title = "Label Color Variations:\n";

# Set colors as configured:
foreach ($cfg as $key => $v) {
    if (!empty($colors[$key])) {
        call_user_func(array($plot, $v['method']), $v['color']);
        $title .= "Color for $key = {$v['color']}\n";
    } else {
        $title .= "No color set for $key\n";
    }
}
$plot->SetTitle($title);

$plot->DrawGraph();
