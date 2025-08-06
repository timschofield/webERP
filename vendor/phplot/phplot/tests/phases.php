<?php

# PHPlot Example: Phases of a sine wave, black background.
require_once 'phplot.php';

# Equation produces n evenly spaced phases of a sine wave.
# Yi(x) = sin(x + i*2*PI/n)
$n = 8;       # Number of lines to draw
$steps = 50;  # Number of steps to generate on each line over [0, 2*PI]

# Generate the data array:
$end = M_PI * 2.0;
$delta = $end / $steps;
$data = array();
$phase_factor = 2 * M_PI / $n;
# Note: Looping on ($x=0; $x<=$end; $x+=$delta) almost works, but it misses
# the last point due to round-off error accumalation.
for ($k = 0; $k <= $steps; $k++) { # Make steps+1 points
  $x = $k * $delta; # More accurate than $x += $delta
  $row = array('', $x);
  for ($i = 0; $i < $n; $i++) {
    $row[] = sin($x + $i * $phase_factor);
  }
  $data[] = $row;
}

$plot = new Phplot\Phplot\phplot(1000, 800);
$plot->SetPlotType('linepoints');
$plot->SetDataType('data-data');
$plot->SetDataValues($data);

$plot->SetTitle("Line Plot, $n phases of Sin(x)");
$plot->SetPlotAreaWorld(0, -1, 2 * M_PI, 1);  # Range: [0,-1] : [2 Pi, 1]
$plot->SetXTickIncrement(M_PI / 8.0);         # X tick step
$plot->SetYTickIncrement(0.2);                # Y tick step
$plot->SetXLabelType('data', 3);              # Format X tick labels as N.NNN
$plot->SetLineStyles('solid');                # Make all lines solid
$plot->SetYLabelType('data', 1);              # Format Y tick labels as N.N
$plot->SetDrawXGrid(True);                    # Draw X grid lines
$plot->SetDrawYGrid(True);                    # Draw Y grid lines

# Because one of the data colors is almost white, switch to black background:
$plot->SetBackgroundColor('black');
$plot->SetTitleColor('white');
$plot->SetTextColor('white');
$plot->SetGridColor('white');

$plot->DrawGraph();
