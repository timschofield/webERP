<?php

# Unit test for CalcPlotAreaWorld()
# This tries a variety of ranges and does some simple checks on the results.
require_once 'phplot.php';

# True to report test cases and all results:
$test_verbose = False;

# For validation, need to know the min and max tick interval range.
$test_min_ticks = 8;
# In theory max = min * 2.5, but allow for 1 more.
$test_max_ticks = (int)ceil($test_min_ticks * 2.5) + 1;

# Global test case counters:
$n_tests = 0;
$n_pass = 0;
$n_fail = 0;

# This test is specific to behavior at PHPlot >= 6.0.0
if (!defined('Phplot\Phplot\phplot::version_id') || Phplot\Phplot\phplot::version_id < 60000) {
    echo "Skipping test because it was designed for version >=6.0.0\n";
    exit(2);
}

# ====== Test support functions ======

# Echo in verbose mode only:
function vecho($s)
{
    global $test_verbose;
    if ($test_verbose) echo $s;
}

# Extend PHPlot to access protected methods and variables for testing:
class PHPlot_test extends Phplot\Phplot\phplot
{
    # CalcPlotAreaWorld + Prerequisites
    function test_CalcPlotAreaWorld()
    {
        # Prerequisites for CalcPlotAreaWorld, copied from DrawGraph()
        $this->CheckDataArray();
        $this->SetColorIndexes();
        $this->FindDataLimits();
        # Function under test:
        return $this->CalcPlotAreaWorld();
    }

    # Get some protected variables:
    function test_GetVars(&$v)
    {
        $v['plot_xmin'] = $this->plot_min_x;
        $v['plot_xmax'] = $this->plot_max_x;
        $v['plot_ymin'] = $this->plot_min_y;
        $v['plot_ymax'] = $this->plot_max_y;
        $v['xtick'] = $this->x_tick_inc;
        $v['ytick'] = $this->y_tick_inc;
    }
}

# Show test case input, and add the header at intervals.
function testin($q)
{
    static $did_header = 0;
    extract($q); // See variable names below in test()
    if ($did_header <= 0) {
        echo <<<END
 Input:  Xmin    Xmax    Ymin    Ymax
Output:  Xmin    Xmax    Ymin    Ymax    Xtick   Ytick   nXTick  nYTick
        ------- ------- ------- ------- ------- ------- ------- ------- 

END;
        $did_header = 10;
    }
    printf(" Input: %7.2f %7.2f %7.2f %7.2f\n",
           $data_xmin, $data_xmax, $data_ymin, $data_ymax);
    $did_header--;
}

# Show test case output
function testout($q)
{
    extract($q); // See variable names below in test()
    printf("Output: %7.2f %7.2f %7.2f %7.2f %7.2f %7.2f %7d %7d\n\n",
        $plot_xmin, $plot_xmax, $plot_ymin, $plot_ymax,
        $xtick, $ytick, $nxtick, $nytick);
}

# Validate test results:
# Returns an empty string if OK, else a message.
function validate($q)
{
    global $test_min_ticks, $test_max_ticks;

    extract($q); // See variable names below in test()

    $errors = array();

    // Make sure the plot range includes the data range:
    if ($data_xmin < $plot_xmin || $plot_xmax < $data_xmax
       || $plot_xmin > $plot_xmax)
        $errors[] = 'Range error in X';
    if ($data_ymin < $plot_ymin || $plot_ymax < $data_ymax
       || $plot_ymin > $plot_ymax)
        $errors[] = 'Range error in Y';

    // Make sure the tick step is smaller than the data range
    if ($xtick > ($plot_xmax - $plot_xmin))
        $errors[] =  'X tick step error';
    if ($ytick > ($plot_ymax - $plot_ymin))
        $errors[] =  'Y tick step error';

    // Check the number of tick intervals:
    if ($nxtick < $test_min_ticks || $nxtick > $test_max_ticks)
        $errors[] =  'X tick count error';
    if ($nytick < $test_min_ticks || $nytick > $test_max_ticks)
        $errors[] =  'Y tick count error';

    return implode(', ', $errors);
}

# ===== Testing =====

# This is a basic test of plot range calculation, with both ends default
# to automatic, data-data (explicit X), and all default options.
function test($data_xmin, $data_xmax, $data_ymin, $data_ymax)
{
    global $test_verbose, $n_tests, $n_pass, $n_fail;

    $n_tests++;
    $q = compact('data_xmin', 'data_ymin', 'data_xmax', 'data_ymax');

    $title = "($data_xmin, $data_ymin) : ($data_xmax, $data_ymax)";
    if ($test_verbose) testin($q);

    $p = new phplot_test();
    $p->SetDataValues(array(array('', $data_xmin, $data_ymin),
                            array('', $data_xmax, $data_ymax)));
    $p->SetDataType('data-data');
    $p->SetPlotType('lines');
    $p->test_CalcPlotAreaWorld();

    # This returns an array with several protected variables: plot_xmin,
    # plot_xmax, plot_ymin, plot_ymax, xtick, ytick:
    $p->test_GetVars($q);
    # Calculate number of tick intervals:
    $q['nxtick'] = (int)(1.001 * ($q['plot_xmax'] - $q['plot_xmin'])
                                  / $q['xtick']);
    $q['nytick'] = (int)(1.001 * ($q['plot_ymax'] - $q['plot_ymin'])
                                  / $q['ytick']);

    if ($test_verbose) testout($q);

    $result = validate($q);
    if (empty($result)) {
        $n_pass++;
    } else {
        $n_fail++;
        echo "Failed test case $n_tests: $title\n$result\n";
    }
}

# Test cases: (XDataMin  XDataMax  YDataMin  YDataMax)

vecho("=== Base cases\n");
test(0, 100, 0, 1000);
test(-10, 0, -10, 10);
test(3, 983, 17, 54);
test(0, 0.025, 0, 0.025);

vecho("=== Range step\n");
for ($i = 0; $i < 100; $i++) test($i, 100, 0, $i+1);

vecho("=== Crossing zero cases\n");
#   case A)   0 < plot_min < plot_max              0  [-------]
test(5, 110, 5, 110);
#   case B)   0 == plot_min < plot_max             0-------]
test(0, 110, 0, 110);
#   case C)   plot_min < 0 < plot_max         [----0----]
test(-60, 60, -60, 60);
#   case D)   plot_min < plot_max == 0     [-------0
test(-60, 0, -60, 0);
#   case E)   plot_min < plot_max < 0    [-----]   0
test(-60, -20, -60, -20);

vecho("=== Powers of 10 ranges\n");
for ($r = 0.01; $r <= 100; $r *= 10) test(0, $r, 0, $r);
for ($r = 0.01; $r <= 100; $r *= 10) test(-$r, 0, -$r, 0);
for ($r = 0.01; $r <= 100; $r *= 10) test(-$r, $r, -$r, $r);

vecho("=== Negative vs positive ranges\n");
test(10, 100, 10, 100);
test(-100, -10, -100, -10);
test(-100, 10, -100, 10);
test(-10, 100, -10, 100);

vecho("=== Regressive cases\n");
test(0, 0, 0, 0);
test(100, 100, -100, -100);
test(-100, -100, 100, 100);

# ======== End of test cases and error reporting ==========
echo basename(__FILE__)
    . " results: $n_tests test cases, $n_pass pass, $n_fail fail\n";
if ($n_fail > 0) exit(1);
