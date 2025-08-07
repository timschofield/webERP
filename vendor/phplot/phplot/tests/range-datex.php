<?php

# PHPlot test: Date/time range on X axis

#            H, M, S, mo, da, yr
$t1 = mktime(0, 0, 0, 1, 1, 2000);
$t2 = $t1 + 960;

# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'xmin' => $t1,               # Lowest X data value
  'xmax' => $t2,               # Highest X data value
  'mintick' => NULL,           # X minimum tick count
  'xtickinc' => NULL,          # X tick increment
  'tickanchor' => NULL,        # XTick anchor, if set
        ), $tp);

require_once 'phplot.php';

# Extract all test parameters as local variables:
extract($tp);

$dtformat = '%Y-%m-%d %H:%M:%S';

# Check for needed methods, depending on test parameters:
if (isset($mintick) && !method_exists('Phplot\Phplot\phplot', 'TuneXAutoTicks')) {
    echo "Skipping test because it requires TuneXAutoTicks()\n";
    exit(2); // Exit code for 'skip'
}

# 2 point data array - PHPlot ranging only cares about min and max.
$data = array( array('', $xmin, 0), array('', $xmax, 100));

# Build a title including the options:
if ($xmin > 86400) {
    # Assume these are dates.
    $range = @strftime($dtformat, $xmin) . ' : ' . @strftime($dtformat, $xmax);
} else {
    $range = "$xmin : $xmax";
}
$title = "Date/time X Auto-Range Test: [$range]";
if (isset($mintick)) $title .= "\nX Min Ticks = $mintick";
if (isset($xtickinc)) $title .= "\nX Tick Increment = $xtickinc";
if (isset($tickanchor)) $title .= "\nX Tick Anchor = $tickanchor";

$p = new Phplot\Phplot\phplot(800, 800);
$p->SetTitle($title);
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');
$p->SetPlotType('lines');

# Format labels as date/time:
$p->SetXLabelType('time', $dtformat);
$p->SetXLabelAngle(90);

if (isset($mintick)) $p->TuneXAutoTicks($mintick);
if (isset($tickanchor)) $p->SetXTickAnchor($tickanchor);
if (isset($xtickinc)) $p->SetXTickIncrement($xtickinc);

$p->DrawGraph();
