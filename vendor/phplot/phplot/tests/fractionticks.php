<?php
# PHPlot test: Fractional tick labels
# This uses binary auto-tick mode on the Y axis, with a small Y range,
# and a custom label formatting function to display the labels as
# common fractions.
require_once 'phplot.php';

# GCD - Greatest Common Divisor
function gcd($a, $b)
{
    do {
      $c = $a % $b;
      $a = $b;
      $b = $c;
    } while ($c > 0);
    return $a;
}

# Format a label as an architectural fraction, using 1/64 as base.
function fract_label($v)
{
    # Add leading sign and make positive:
    if ($v < 0) {
        $result = '-';
        $v *= -1;
    } elseif ($v == 0) {
        $result = '0';
    } else {
        $result = '';
    }

    # Process whole number part.
    # Prepare for space between number and fraction.
    if ($v >= 1) {
        $whole = (int)$v;
        $v -= $whole;
        $result .= $whole;
        $space_between = ' ';
    } else {
        $space_between = '';
    }

    # Process fractional part:
    if ($v > 0) {
        $p64 = (int)round($v * 64);
        $factor = gcd($p64, 64);
        $numerator = $p64 / $factor;
        $denominator  = 64 / $factor;
        $result .= $space_between . $numerator . '/' . $denominator;
    }
    return $result;
}


$data = array();
$nx = 32;
for ($x = 0; $x <= $nx; $x++) {
    $y = sin(2 * $x * M_PI / $nx);
    $data[] = array('', $x, $y);
}

if (!method_exists('Phplot\Phplot\phplot', 'TuneXAutoTicks')) {
    echo "Skipping test because it requires TuneXAutoTicks()\n";
    exit(2); // Exit code for 'skip'
}

$plot = new Phplot\Phplot\phplot(800, 600);

$plot->SetTitle('Powers of 2 X Tick Increment, Fractional Y Tick Labels');

$plot->SetDataType('data-data');
$plot->SetDataValues($data);
$plot->SetPlotType('lines');

# Set minimum 25 ticks, binary mode steps.
$plot->TuneYAutoTicks(25, 'binary');
$plot->SetYLabelType('custom', 'fract_label');

# Use binary ticks on X too:
$plot->TuneXAutoTicks(NULL, 'binary');

$plot->DrawGraph();
