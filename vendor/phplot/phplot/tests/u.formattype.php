<?php

# PHPlot unit test: Format Types for labels
# This test verifies the examples on the reference page for SetXLabelType.
# It also tests 2-format and 3-format printf extensions in PHPlot-6.2.
require_once 'phplot.php';
require_once 'usupport.php';

# True to report test cases and all results:
$test_verbose = False;

# Global test case counters:
$n_tests = 0;
$n_pass = 0;
$n_fail = 0;
$n_skip = 0;

# Extend PHPlot class to allow access to label formatting function:
class PHPlot_test extends Phplot\Phplot\phplot
{
    // Set label type. Arbitrarily uses 'x' label formatting.
    function set_label_type() // Variable args
    {
        $argv = func_get_args();
        return $this->SetLabelType('x', $argv);
    }

    // Format a label. Does not support custom format w/extra args.
    function format_label($value) // Variable args
    {
        return $this->FormatLabel('x', $value);
    }
}

# ====== Test functions ======

# Check if multiple printf formats is supported, and skip cases if not.
function check_supported_3printf($cases)
{
    global $test_verbose, $n_tests, $n_skip;

    # Make sure PHPlot supports multiple printf formats:
    if (Phplot\Phplot\phplot::version_id < 60200) {
        $n_cases = count($cases) / 2;
        if ($test_verbose)
            echo "Skipping $n_cases cases due to unsupported feature\n";
        $n_tests += $n_cases;
        $n_skip += $n_cases;
        return FALSE;
    }
    return TRUE;
}

# Common bottom of test*() functions
# $cases is an array with pairs of 'input' and 'expected' values.
function test_cases($plot, $cases)
{
    global $test_verbose, $n_tests, $n_pass, $n_fail;

    $i = 0;
    while (array_key_exists($i+1, $cases)) {
        $input = $cases[$i];
        $expected = $cases[$i+1];
        $n_tests++;
        $title = "  Case $n_tests: format '$input'";
        $error = '';
        $result = $plot->format_label($input);
        if ($test_verbose) echo "$title => '$result'\n";
        if (expect_equal($expected, $result, $title, $error)) {
            $n_pass++;
        } else {
            $n_fail++;
            echo "$error\n";
        }
        $i = $i + 2;
    }
}

# Test printf formatting with 1 format string.
# $cases is an array with input => expected_result.
function test_printf($format, $cases)
{
    global $test_verbose;

    if ($test_verbose)
        echo "Testing: Single printf format '$format'\n";
    $p = new phplot_test;
    $p->set_label_type('printf', $format);
    test_cases($p, $cases);
}

# Test printf formatting with 2 format strings, any number of values.
# $cases is an array with input => expected_result.
function test_2printf($format1, $format2, $cases)
{
    global $test_verbose;

    # Make sure PHPlot supports multiple printf formats:
    if (!check_supported_3printf($cases)) return;

    if ($test_verbose)
        echo "Testing: Double printf formats '$format1' '$format2'\n";
    $p = new phplot_test;
    $p->set_label_type('printf', $format1, $format2);
    test_cases($p, $cases);
}

# Test printf formatting with 3 format strings, any number of values.
# $cases is an array with input => expected_result.
function test_3printf($format1, $format2, $format3, $cases)
{
    global $test_verbose;

    # Make sure PHPlot supports multiple printf formats:
    if (!check_supported_3printf($cases)) return;

    if ($test_verbose)
        echo "Testing: Triple printf formats '$format1' '$format2' '$format3'\n";
    $p = new phplot_test;
    $p->set_label_type('printf', $format1, $format2, $format3);
    test_cases($p, $cases);
}

# Test data formatting, any number of values.
# $cases is an array with input => expected_result.
# This forces number format to US standard too.
function test_data($prec, $prefix, $suffix, $cases)
{
    global $test_verbose;

    if ($test_verbose)
        echo "Testing: 'data', prec=$prec prefix=$prefix suffix=$suffix\n";
    $p = new phplot_test;
    $p->SetNumberFormat('.', ',');
    $p->set_label_type('data', $prec, $prefix, $suffix);
    test_cases($p, $cases);
}

# Test data formatting
# $cases is an array with input => expected_result.
function test_data2($prec, $decimal, $thousands, $prefix, $suffix, $cases)
{
    global $test_verbose;

    if ($test_verbose)
        echo "Testing: 'data' (number format $decimal $thousands)",
             " prec=$prec prefix=$prefix suffix=$suffix\n";
    $p = new phplot_test;
    $p->SetNumberFormat($decimal, $thousands);
    $p->set_label_type('data', $prec, $prefix, $suffix);
    test_cases($p, $cases);
}

# Test date/time formatting
# $cases is an array with input => expected_result.
function test_time($format, $cases)
{
    global $test_verbose;

    if ($test_verbose)
        echo "Testing: 'time' format '$format'\n";
    $p = new phplot_test;
    $p->set_label_type('time', $format);
    test_cases($p, $cases);
}

# Test custom formatting. Does not support pass-through arguments.
function test_custom($fname, $cases)
{
    global $test_verbose;

    if ($test_verbose)
        echo "Testing: 'custom' with function '$fname'\n";
    $p = new phplot_test;
    $p->set_label_type('custom', $fname);
    test_cases($p, $cases);
}

# Custom label format function used by test cases below, from the manual:
function deg_min_sec($value)
{
  $deg = (int)$value;
  $value = ($value - $deg) * 60;
  $min = (int)$value;
  $sec = (int)(($value - $min) * 60);
  return "{$deg}d {$min}m {$sec}s";
}

# ===== Test Cases =====

# Cases from the reference manual page on SetXLabelType():

test_data(2, '', '', array(
    1234.56, "1,234.56",
    3.14159, "3.14"
));

// Expected result is an actual Euro symbol, but GD does that, not PHP,
// so instead compare with the named entity.
test_data(0, '&#8364;', '', array(
    1000000,  "&#8364;1,000,000",
));

// These will look wrong without a Unicode font and terminal, but will work:
test_data2(2, ',', '.', '', "\xe2\x82\xac", array(
    1e6, "1.000.000,00\xe2\x82\xac",
    4321.123, "4.321,12\xe2\x82\xac",
));

test_time('%m/%Y', array(
    1208232000, "04/2008",
));

test_printf('%8.2e', array(
    1234, " 1.23e+3",
));

test_2printf('%.2f', '(%.2f)', array(
    15.6, "15.60",
   -9.87, "(9.87)",
));

test_3printf('GAIN:$%.2f', 'LOSS:($%.2f)', '[Unchanged]', array(
     9.1,  'GAIN:$9.10',
  -26.35,  'LOSS:($26.35)',
       0,  '[Unchanged]',
));

test_custom('deg_min_sec', array(
    75.12345, "75d 7m 24s",
     0,       "0d 0m 0s",    // Extra
   136.5,     "136d 30m 0s", // Extra
));

# Additional 1, 2, and 3-format printf cases:

test_printf('%4d', array(
      -10,  " -10",
       -1,  "  -1",
        0,  "   0",
        1,  "   1",
       10,  "  10",
    10000,  "10000",
     M_PI,  "   3",
));

test_2printf('%d', '(%d)', array(
      -10,  "(10)",
       -1,  "(1)",
        0,  "0",
        1,  "1",
       10,  "10",
     M_PI,  "3",
));

test_3printf('%.2f', '*-%.2f', 'ZERO', array(
      -10,  "*-10.00",
       -1,  "*-1.00",
        0,  "ZERO",
        1,  "1.00",
       10,  "10.00",
     M_PI,  "3.14",
 -M_SQRT2,  "*-1.41",
));

test_printf('<%.2e>', array(
 91234.56,  "<9.12e+4>",
-91234.56,  "<-9.12e+4>",
  0.00456,  "<4.56e-3>",
 -0.00456,  "<-4.56e-3>",
     M_PI,  "<3.14e+0>",
 -M_SQRT2,  "<-1.41e+0>",
        0,  "<0.00e+0>",
));

test_2printf('%.2e', '(%.2e)', array(
 91234.56,  "9.12e+4",
-91234.56,  "(9.12e+4)",
  0.00456,  "4.56e-3",
 -0.00456,  "(4.56e-3)",
     M_PI,  "3.14e+0",
 -M_SQRT2,  "(1.41e+0)",
        0,  "0.00e+0",
));

test_3printf('POS %.3e', 'NEG %.3e', 'Z', array(
 91234.56,  "POS 9.123e+4",
-91234.56,  "NEG 9.123e+4",
  0.00456,  "POS 4.560e-3",
 -0.00456,  "NEG 4.560e-3",
     M_PI,  "POS 3.142e+0",
 -M_SQRT2,  "NEG 1.414e+0",
        0,  "Z",
));

# ======== End of test cases and error reporting ==========

echo basename(__FILE__),
    " results: $n_tests test cases,",
    " $n_pass pass, $n_skip skipped, $n_fail fail\n";
if ($n_fail > 0) exit(1);
# PHPlot test suite requires falling off the end, not exit, on success.
