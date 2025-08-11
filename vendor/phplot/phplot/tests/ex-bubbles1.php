<?php

# PHPlot Example - Bubble Plot
require_once 'phplot.php';

# Array of custom labels for the Y axis. See the get_label callback.
$y_labels = array("", "Age\n12 and under", "Age 13-17", "Age 18-29",
                      "Age 30-39", "Age 40-54", "Age\n55 and older");

# Return the string for a Y label:
function get_label($value, $labels)
{
    if (isset($labels[(int)$value])) return $labels[(int)$value];
    return $value;
}

#                       <=12    13-17   17-28   30-39   40-54    >=55
$data = array(
    array('Cherry', 1,   1, 2,   2, 4,   3, 3,   4, 3,   5, 5,   6, 6),
    array('Apple',  2,   1, 9,   2, 7,   3, 4,   4, 7,   5, 3,   6, 7),
    array('Pear',   3,  '', 2,   2, 2,   3, 3,   4, 4,   5, 3,   6, 2),
    array('Grape',  4,   1, 8,   2, 5,   3, 5,   4, 6,   5, 3,   6, 4),
    array('Kiwi',   5,  '', 0,   2, 3,   3, 4,   4, 4,   5, 5,   6, 2),
    array('Banana', 6,   1, 5,   2, 4,   3, 6,   4, 3,   5, 3,   6, 4),
);

$plot = new Phplot\Phplot\phplot(600, 600);
$plot->SetTitle("Flavor Preference By Age Group");
$plot->SetDataType('data-data-xyz');
$plot->SetDataValues($data);
$plot->SetPlotType('bubbles');
$plot->SetDataColors('yellow'); // Use same color for all data sets
$plot->SetDrawPlotAreaBackground(True);
$plot->SetPlotBgColor('plum');
$plot->SetLightGridColor('red'); // Change grid color to make it visible
$plot->SetImageBorderType('plain');
$plot->SetPlotBorderType('full');
$plot->SetXTickIncrement(1); // For grid line spacing
$plot->SetYTickIncrement(1);
$plot->SetPlotAreaWorld(0, 0, 6.5, 6.5);
# Establish the handler for the Y label text:
$plot->SetYLabelType('custom', 'get_label', $y_labels);
$plot->SetXTickPos('both'); // Tick marks on both sides
$plot->SetYTickPos('both'); // Tick marks on top and bottom too
$plot->SetXDataLabelPos('both'); // X axis data labels top and bottom
$plot->SetYTickLabelPos('both'); // Y axis labels left and right
$plot->SetDrawXGrid(True);
$plot->DrawGraph();
