<?php

# PHPlot test: Plot auto-range test
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'subtitle' => '',        # Plot title additional line
  'min' => 0,              # Lowest data value
  'max' => 100,            # Highest data value
  'zm' => NULL,            # Range zero magnet
  'adjust_mode' => NULL,   # Range adjust mode: 'T', 'R', or 'I'
  'adjust_amount' => NULL, #  % of range for adjustment
  'mintick' => NULL,       # Minimum tick count
  'tick_mode' => NULL,     # Tick selection mode
  'intinc' => NULL,        # Integer increment flag
  'tickanchor' => NULL,    # Tick anchor, if set
  'plot_type' => 'linepoints',  # Plot type, must work with data-data
  'n' => 10,               # Number of data points
        ), $tp);

require_once 'phplot.php';

# Extract all test parameters as local variables:
extract($tp);

# Check for needed methods, depending on test parameters:
$uses['TuneYAutoRange'] =
   isset($zm) || isset($adjust_mode) || isset($adjust_amount);
$uses['TuneYAutoTicks'] =
   isset($mintick) || isset($tick_mode) || isset($intinc);

foreach ($uses as $method => $needed) {
    if ($needed && !method_exists('Phplot\Phplot\phplot', $method)) {
        echo "Skipping test because it requires $method()\n";
        exit(2); // Exit code for 'skip'
    }
}

$data_type = 'data-data'; // Only works with this type

# Build a data array with $n points ranging from $min to $max.
# Use arbitrary X range 0:100.
$data = array();
$x = 0;
$y = $min;
if ($n > 1) {
    $dx = 100 / ($n - 1);
    $dy = ($max - $min) / ($n - 1);
} else {
    $dx = 0; // Not used in this case
    $dy = 0; // Not used in this case
}
for ($i = 0; $i < $n; $i++) {
    $data[] = array('', $x, $y);
    # Make sure the range is really min to max:
    if ($i == $n - 2) $y = $max; else $y += $dy;
    $x += $dx;
}

# Build a title including the options:
$title = "Auto-Range/Auto-Tick $plot_type Test, Range = [$min : $max]";
if (isset($zm)) $title .= "\nAuto-range Zero Magnet = $zm";
if (isset($adjust_mode) || isset($adjust_amount))
    $title .= "\nEnd Adjustment: mode="
           . (isset($adjust_mode) ? $adjust_mode : "NULL")
           . ", amount="
           . (isset($adjust_amount) ? $adjust_amount : "NULL");
if (isset($mintick)) $title .= "\nMin Ticks = $mintick";
if (isset($tick_mode)) $title .= "\nTick Increment Mode = $tick_mode";
if (!empty($intinc)) $title .= "\nForce Integer Step On";
if (isset($tickanchor)) $title .= "\nTick anchor = $tickanchor";
if (!empty($subtitle)) $title .= "\n" . $subtitle;

$p = new Phplot\Phplot\phplot(800, 800);
$p->SetTitle($title);
$p->SetDataType($data_type);
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');
$p->SetPlotType($plot_type);

if (isset($tickanchor)) $p->SetYTickAnchor($tickanchor);

if ($uses['TuneYAutoRange'])
    $p->TuneYAutoRange($zm, $adjust_mode, $adjust_amount);
if ($uses['TuneYAutoTicks'])
    $p->TuneYAutoTicks($mintick, $tick_mode, $intinc);

$p->DrawGraph();
