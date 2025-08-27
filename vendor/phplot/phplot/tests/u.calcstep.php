<?php

# PHPlot unit test: functions used by CalcPlotAreaWorld to calculate tick step
# This checks out CalcStep125(), CalcStepDateTime(), and CalcStepBinary()
require_once 'phplot.php';

# True to report test cases and all results:
$test_verbose = False;

# Global test case counters:
$n_tests = 0;
$n_pass = 0;
$n_fail = 0;

# ====== Test functions ======

# Decode number of seconds to time interval
function interval($sec)
{
    static $convert = array('week', 604800, 'day', 86400,
                            'hour', 3600, 'minute', 60, 'second', 1);
    $result = array();
    reset($convert);
    for ($unit = current($convert); !empty($unit); $unit = next($convert)) {
        $factor = next($convert);
        if ($sec >= $factor) {
            $n = (int)($sec / $factor);
            $result[] = "$n $unit" . ($n == 1 ? '' : 's');
            $sec -= $n * $factor;
        }
    }
    return implode(', ', $result);
}

# Given a range and number of intervals, calculate the number of steps.
# There is some built-in 'fudge' here, otherwise it seems to be below the
# minimum number of tick marks. PHPlot does something similar.
function get_nsteps($range, $nsteps)
{
    return floor($range * 1.0001 / $nsteps);
}

# Extend PHPlot to access protected methods for testing:
class PHPlot_test extends Phplot\Phplot\phplot
{
    function test_CalcStep125($range, $min_ticks)
    {
        return $this->CalcStep125($range, $min_ticks);
    }

    function test_CalcStepDatetime($range, $min_ticks)
    {
        return $this->CalcStepDatetime($range, $min_ticks);
    }

    function test_CalcStepBinary($range, $min_ticks)
    {
        return $this->CalcStepBinary($range, $min_ticks);
    }
}


# Verify that a number meets the "1, 2, 5" rule: That X = K * 10**N
# for some integer N and K = 1, 2, or 5.
# Return true if it passes else false.
function good_125step($x)
{
    $n = (int)floor(log10($x));
    $residue = $x / pow(10, $n);
    # Equality test on floats isn't usually safe, but it works here.
    return ($residue == 1 || $residue == 2 || $residue == 5);
}

# Check if a date/time step is "valid".
# Since the algorithm uses an array of valid values, there is no simple
# math way to validate it.
# The algorithm description is:
#  "For seconds or minutes: 1 2 5 10 15 30. For hours: 1 2 4 8 12 24 48 96 168.
#   For data ranges which would exceed 2.5 x min_ticks x 1 week, fall back to
#   the 1/2/5 method using days as units."
function good_datetimestep($x)
{
    // Valid for seconds and minutes:
    static $good_1 = array(1=>1, 2=>2, 5=>1, 10=>1, 15=>1, 30=>1);
    // Valid for hours:
    static $good_2 = array(1=>1, 2=>2, 4=>1, 8=>1, 12=>1, 24=>1,
                           48=>1, 96=>1, 168=>1);

    if ($x < 60) {  // Up to 1 minute
        return isset($good_1[$x]);
    }
    if ($x % 60 != 0) return FALSE; // Not a whole number of minutes
    $x /= 60; // Now in minutes
    if ($x < 60) {  // Up to 1 hour
        return isset($good_1[$x]);
    }
    if ($x % 60 != 0) return FALSE; // Not a whole number of hours
    $x /= 60; // Now in hours
    if ($x <= 168) { // Up to 1 week
        return isset($good_2[$x]);
    }
    if ($x % 24 != 0) return FALSE; // Not a whole number of days
    $x /= 24; // Now in days
    return good_125step($x); // Revert to 1,2,5 * 10**N
}

# Test good_datetimestep() : Output matching values.
function test_good_datetimestep()
{
   echo "These values are 'good' date/time steps:\n";
   for ($t = 1; $t < 10000000; $t++)
       if (good_datetimestep($t))
           printf("%8d  =  %s\n", $t, interval($t));
}

# Check if a binary (power of 2) step is "valid".
# Using just pow(2, (int)floor(log($x, 2))) does not work.
# It fails for a few values like 8, because of rounding
# error:   log(8,2)-3 ~= -4.44e-16. So there is a fudge factor
# built in below, for values with positive logs.
function good_binary($x)
{
    $v1 = log($x, 2);
    if ($x >= 1) $v1 *= 1.0001;
    $n = (int)floor($v1);
    $error = abs($x - pow(2, $n));
    return ($error == 0);
}

# Test good_binary() : Output matching values.
function test_good_binary()
{
   echo "These values test as good binary steps:\n";
   # Values >= 1
   for ($t = 1; $t < 1000000; $t++)
       if (good_binary($t))
           printf("%8d\n", $t);

   # Values < 1, binary steps only
   $t = 1;
   for ($i = 0; $i <= 32; $i++) {
       $t /= 2;
       if (good_binary($t))
           printf("%15.12f\n", $t);
   }
}


# Test PHPlot CalcStep125 - show valid return values
function test_show_calcstep125()
{
    $p = new phplot_test();

    echo "The following are valid steps returned by CalcStep125():\n";
    $prev_step = -1;
    for ($i = 1; $i < 2000000; $i++) {
        $step = $p->test_CalcStep125($i, 1);
        if ($step != $prev_step) {
            printf("%8d\n", $step);
            $prev_step = $step;
        }
    }
    echo "\n";
}

# Test PHPlot CalcStepdatetime - show valid return values
function test_show_calcstepdatetime()
{
    $p = new phplot_test();

    echo "The following are valid steps returned by CalcDatetime():\n";
    echo "Seconds    Interval\n";
    echo "--------   -----------\n";
    $prev_step = -1;
    for ($i = 1; $i < 2000000; $i++) {
        $step = $p->test_CalcStepDatetime($i, 1);
        if ($step != $prev_step) {
            printf("%8d   %s\n", $step, interval($step));
            $prev_step = $step;
        }
    }
    echo "\n";
}

# Validate a test case result.
#  $mode : Tick step mode: 'decimal', 'date', or 'binary'.
#  $min_ticks, $range : Inputs to CalcStep*()
#  $result : Ouptut from CalcStep*()
function check_result($mode, $min_ticks, $range, $result)
{
    $err = '';

    # Check the number of intervals ("number of ticks", but not quite) is
    # between min and 2.5 x min.
    $nsteps = get_nsteps($range, $result);
    if ($range > $min_ticks) {
        if ($nsteps < $min_ticks) {
            $err .= "  $nsteps ticks is too few\n";
        }
    }
    if ($nsteps > 2.5 * $min_ticks) {
        $err .= "  $nsteps ticks is too many\n";
    }

    # Verify per-mode rule: decimal(1/2/5*10^N), binary(2^N), or date/time:
    if ($mode == 'date') {
        # Special case for range too small - this will violate min_ticks
        # so don't check with good_datetimestep():
        if ($range < $min_ticks) {
            if ($result != 1)
                $err .= "  Datetime interval $result should be 1 sec\n";
        } else {
            if (!good_datetimestep($result))
                $err .= "  Datetime interval $result is not accepted\n";
        }
    } elseif ($mode == 'binary') {
        if (!good_binary($result)) {
            $err .= "  Binary interval $result is not accepted\n";
        }
    } else {
        if (!good_125step($result)) {
            $err .= "  Decimal interval $result is not accepted\n";
        }
    }
    if (empty($err)) return '';
    return "Errors with case (range=$range, min_ticks=$min_ticks):\n$err";
}

# Verbose output - table with periodic headers.
function report($mode, $range, $min_ticks, $result, $note = '')
{
    static $header = 0, $format;
    global $test_verbose, $n_tests;

    if (!$test_verbose) return;
    if ($header-- == 0) {
        $header = -1;  // Set to # lines per header, or -1 for once only.
        echo <<<END
Case Mode        Range     MinTicks => Interval     #Steps Note
---- -------- ------------ --------    ------------ ------ --------------

END;
        $format = "%4d %-8s %12g %8d => %12g %6d %s\n";
    }
    printf($format, $n_tests, $mode, $range, $min_ticks,
           $result, get_nsteps($range, $result), $note);
}

# Echo in verbose mode only:
function vecho($s)
{
    global $test_verbose;
    if ($test_verbose) echo $s;
}

# Test decimal case:
function tn($range, $min_ticks)
{
    global $p, $n_tests, $n_pass, $n_fail;
    $n_tests++;
    $result = $p->test_CalcStep125($range, $min_ticks);
    report('Decimal', $range, $min_ticks, $result);
    $err = check_result('decimal', $min_ticks, $range, $result);
    if (empty($err)) $n_pass++;
    else {
        $n_fail++;
        echo $err;
    }
}

# Test datetime case:
function td($range, $min_ticks)
{
    global $p, $n_tests, $n_pass, $n_fail;
    $n_tests++;
    $result = $p->test_CalcStepDatetime($range, $min_ticks);
    report('Date', $range, $min_ticks, $result, interval($result));
    $err = check_result('date', $min_ticks, $range, $result);
    if (empty($err)) $n_pass++;
    else {
        $n_fail++;
        echo $err;
    }
}

# Test binary case:
function tb($range, $min_ticks)
{
    global $p, $n_tests, $n_pass, $n_fail;
    $n_tests++;
    $result = $p->test_CalcStepBinary($range, $min_ticks);
    report('Binary', $range, $min_ticks, $result);
    $err = check_result('binary', $min_ticks, $range, $result);
    if (empty($err)) $n_pass++;
    else {
        $n_fail++;
        echo $err;
    }
}


# ===== Setup =====

$p = new phplot_test();
if (!method_exists('Phplot\Phplot\phplot', 'CalcStep125')
 || !method_exists('Phplot\Phplot\phplot', 'CalcStepBinary')
 || !method_exists('Phplot\Phplot\phplot', 'CalcStepDateTime')) {
    echo "Skipping test because it requires CalcStep125, "
         . "CalcStepBinary, and CalcStepDateTime\n";
    exit(2); // Exit code for 'skip'
}

# ===== Test Cases =====

# Decimal mode tests:
function test_decimal()
{

    vecho("Decimal mode: Powers of 2 from -16 to 16\n");
    for ($r = 1/32768; $r <= 32768; $r *= 2) tn($r, 5);

    vecho("Decimal mode: Powers of 10 from -9 to 9\n");
    for ($r = 1e-9; $r < 1e9; $r *= 10) tn($r, 10);

    vecho("Decimal mode: From 1 to 100 with min 3, showing step points\n");
    for ($r = 1; $r <= 100; $r++) tn($r, 3);

    vecho("Decimal mode: Changing minsteps from 2 thru 20\n");
    for ($min = 2; $min <= 20; $min += 2) tn(197, $min);
}


# Date/time mode tests:
function test_datetime()
{

    vecho("Date/time mode: From 3 to 100 with min 3\n");
    for ($r = 3; $r <= 100; $r++) td($r, 3);

    vecho("Date/time mode: Powers of 2\n");
    for ($r = 1; $r < 1e9; $r *= 2) td($r, 5);
}

# Binary mode tests:
function test_binary()
{

    vecho("Binary mode: Positive powers of 2 to 30\n");
    $r = 1;
    for ($n = 0; $n <= 30; $n++) {
       tb($r, 8);
       $r *= 2;
    }
    vecho("Binary mode: Negative powers of 2 to -30\n");
    $r = 1;
    for ($n = 0; $n <= 30; $n++) {
       tb($r, 8);
       $r /= 2;
    }

    vecho("Binary mode: From 1 to 100 with min 5, showing step points\n");
    for ($r = 1; $r <= 100; $r++) tb($r, 5);

    vecho("Binary mode: Changing minsteps from 2 thru 20\n");
    for ($min = 2; $min <= 20; $min += 2) tb(252, $min);
}

# Decimal exact test:
# Any value K = 1, 2, or 5 * 10^N with min_ticks=1 should return that
# value K within a small margin.
# (Note tick steps are max 1:2.5 ratio in this mode, so an error is either
# 50% or more, or there is no error.)

# Test one case:
function td1($range)
{
    global $p, $n_tests, $n_pass, $n_fail, $test_verbose;

    $n_tests++;
    $result = $p->test_CalcStep125($range, 1);

    if ((abs($result - $range) / $range) > 0.01) {
        $err = sprintf(" Error: %9.3e", $range - $result);
        $n_fail++;
    } else {
        $err = "";
        $n_pass++;
    }

    # Report results in compact form if failed, or if verbose mode:
    if ($test_verbose || !empty($err))
        printf("Decimal exact step %9.3e => %9.3e%s\n", $range, $result, $err);
}

# Decimal Exact test: test a range
function test_decimal_exact()
{
    vecho("Decimal, positive powers of 10 * 1, 2, 5\n");
    $n = 1;
    $k = 1;
    for ($i = 0; $i < 60; $i++) {
        td1($k * $n);
        if ($k == 1) $k = 2;
        elseif ($k == 2) $k = 5;
        else {
          $k = 1;
          $n *= 10;
        }
    }

    vecho("\nDecimal, negative powers of 10 * 1, 2, 5\n");
    $n = 1;
    $k = 1;
    for ($i = 0; $i < 60; $i++) {
        td1($k * $n);
        if ($k == 1) {
          $k = 5;
          $n /= 10;
        } elseif ($k == 2) $k = 1;
        else $k = 2;
    }
    vecho("\n");
}

# Binary exact test:
# Any value K = 2^N with min_ticks=1 should return that value K within a
# small margin.
# (Note tick steps are 1:2 ratio in this mode, so an error is either 50% or
# more, or there is no error.)
# Test one case:
function tb1($range)
{
    global $p, $n_tests, $n_pass, $n_fail, $test_verbose;

    $n_tests++;
    $result = $p->test_CalcStepBinary($range, 1);

    if ((abs($result - $range) / $range) > 0.01) {
        $err = sprintf(" Error: %9.3e", $range - $result);
        $n_fail++;
    } else {
        $err = "";
        $n_pass++;
    }

    # Report results in compact form if failed, or if verbose mode:
    if ($test_verbose || !empty($err))
        printf("Binary exact step %9.3e => %9.3e%s\n", $range, $result, $err);
}

# Binary Exact test: test a range
function test_binary_exact()
{
    vecho("Binary, positive powers of 2\n");
    $n = 1;
    for ($i = 0; $i < 30; $i++) {
        tb1($n);
        $n *= 2;
    }

    vecho("\nBinary, negative powers of 2\n");
    $n = 1;
    # let's reduce the precision of the test for php <= 7.4, from 30 to 29 divisions
    for ($i = 0; $i < 29; $i++) {
        tb1($n);
        $n /= 2;
    }
    vecho("\n");
}


# ======== Run Tests ======

# These are for debugging the test itself:
# test_show_calcstep125();
# test_good_datetimestep();
# test_show_calcstepdatetime();
# test_good_binary();


test_decimal();
test_binary();
test_datetime();
test_decimal_exact();
test_binary_exact();

# ======== End of test cases and error reporting ==========

echo basename(__FILE__)
    . " results: $n_tests test cases, $n_pass pass, $n_fail fail\n";
if ($n_fail > 0) exit(1);
# PHPlot test suite requires falling off the end, not exit, on success.
