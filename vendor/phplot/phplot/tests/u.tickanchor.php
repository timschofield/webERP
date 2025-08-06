<?php

# Unit test for X/Y tick anchors
require_once 'phplot.php';
$cases = 0;
$errors = 0;
$verbose = False;

// Extend PHPlot class to allow access to protected function CalcTicks:
class PHPlot_test extends Phplot\Phplot\phplot
{
    function test_CalcTicks($which)
    {
        return $this->CalcTicks($which);
    }
}

// Run one test case
function test($start, $step, $anchor)
{
    static $need_header = 0;
    global $cases, $errors, $verbose;

    $plot = new phplot_test();
    // Minimal plot setup. Data values do not matter - only CalcTicks is used.
    $plot->SetPlotAreaWorld(NULL, $start, NULL, $start + 10 * $step);
    $plot->SetYTickIncrement($step);
    $plot->SetYTickAnchor($anchor);
    $plot->SetDataValues(array(array('', $start), array('', $start+1)));
    $plot->SetDataType('text-data');
    $plot->SetPlotType('points');
    $plot->SetPrintImage(False);
    $plot->DrawGraph();

    // Get the actual values from CalcTicks() for Y:
    list($tick_start, $tick_end, $tick_step) = $plot->test_CalcTicks('y');
    $cases++;

    // Check results:
    $result = '';
    // Adjusted tick mark must not be to the left of the original:
    if ($tick_start < $start) {
        $result .= ' Wrong direction;';
    // Adjusted tick mark must not be more than 1 tick mark right of original:
    } elseif ($tick_start >= $start + $step) {
        $result .= ' Too far;';
    }
    // There should be an integer number of steps between start and anchor.
    // But allow for some fuzz.
    $n_tick = ($tick_start - $anchor) / $step;
    if ($n_tick > 0 && (($n_tick - (int)$n_tick) / $n_tick) > 0.005) {
        $result .= ' No tick @ anchor;';
    }

    // Report results:
    if (empty($result)) $status = 'OK';
    else {
        $status = 'Error:';
        $errors++;
    }

    if ($verbose) {
        if ($need_header == 0) {
            $need_header = 20;
            echo "Start    Step     Anchor   => Adjusted  Result:\n";
            echo "-------- -------- -------- => --------  ------------\n";
        }
        printf("%8g %8g %8g => %8g  %s\n",
              $start, $step, $anchor, $tick_start, $status . $result);
        $need_header--;
    }
    if (!empty($result)) {
        fwrite(STDERR, "Error: start=$start step=$step anchor=$anchor\n");
        fwrite(STDERR, "      Result=$tick_start   Error=$result\n");
    }
}

// Test cases:  (start, step, anchor)
test(0, 10, -5);
test(0, 10, 2.3);
test(0, 10, 542);
test(-5, 2, 0);

for ($anchor = -20; $anchor <= 20; $anchor++) test(0, 10, $anchor);
for ($anchor = 0; $anchor <= 10; $anchor++) test(-10, 3, $anchor);

for ($start = -20; $start <= 20; $start++) test($start, 7, 0);

for ($step = 1; $step <= 1000; $step *= 10)
    for ($anchor = 0; $anchor <= 10; $anchor++)
        test(0, $step, $anchor);

for ($step = 0.001; $step <= 10; $step *= 2) test(-14.2857, $step, 1);

for ($anchor = 0.0001; $anchor <= 10000; $anchor *= 10) test(1, 10, $anchor);

// Results:
echo "u.tickanchor test cases: $cases, Errors: $errors\n";
if ($errors > 0) exit(1);
