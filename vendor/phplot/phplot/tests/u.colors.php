<?php

# Unit tests for color functions
# This doesn't actually output a graph. It checks internal functions.

# Note: PHPlot-5.1.1 introduced truecolor images, using a new class,
# but SetRGBColor() still returned a 3 element array (r,g,b) for
# the base class. Starting a few CVS revs after 5.1.1, SetRGBColor() will
# return a 4 element array for both base and truecolor class. The test
# below automatically detects the return style and adapts to it.

require_once 'phplot.php';
require_once 'usupport.php';

# True to report test cases and all results:
$test_verbose = False;

# Global test case counters:
$n_tests = 0;
$n_pass = 0;
$n_fail = 0;

# ====== Test functions ======

# Initialization:
# Creates the global PHPlot object, and determines which form of
# SetRGBColor() exists: older, returns RGB, or newer, returns RGBA.
function test_init()
{
    global $returns_alpha, $p, $test_verbose;

    $p = new Phplot\Phplot\phplot(); // Global

    # Determine if SetRGBColor returns a 3 or 4 element array:
    $test_array = $p->SetRGBColor('');
    if (count($test_array) == 3) {
        $style = "3 element arrays (old style)";
        $returns_alpha = False;
    } elseif (count($test_array) == 4) {
        $style = "4 element arrays (new style)";
        $returns_alpha = True;
    } else {
        fwrite(STDERR, "u.colors: Unexpected return style from SetRGBArray\n");
        exit(1);
    }
    if ($test_verbose) echo "Checking SetRGBColor return: $style\n";
}

# Return a 3- or 4- element array for testing, depending on the flag which
# is set based on version of SetRGBColor():
function xd($r, $g, $b)
{
    global $returns_alpha;
    if ($returns_alpha) return array($r, $g, $b, 0);
    return array($r, $g, $b);
}

# Execute one test case.
#  $name : Name or title of the test case
#  $expected : Expected result, 3 or 4 element array (see xd() above)
#  $arg : Argument to SetRGBColor
# Increments the test, pass, and fail counters based on the results.
function test($name, $expected, $arg)
{
    global $p, $test_verbose, $n_tests, $n_pass, $n_fail;

    $n_tests++;
    $title = "Test case $n_tests: $name";
    if ($test_verbose) echo "$title\n";

    $error = '';
    if (expect_equal($expected, $p->SetRGBColor($arg), $title, $error)) {
        $n_pass++;
    } else {
        $n_fail++;
        echo "$error\n";
    }
}

# ===== Test Cases =====

test_init();

test('SetRGBColor empty string', xd(0,0,0), '');

test('SetRGBColor #rgb black', xd(0,0,0), '#000000');

test('SetRGBColor #rgb white', xd(255,255,255), '#ffffff');

test('SetRGBColor #rgb white upper case', xd(255,255,255), '#FFFFFF');

test('SetRGBColor array', xd(20,30,40), array(20, 30, 40));

test('SetRGBColor black array', xd(0,0,0), array(0, 0, 0));

# Named colors from the RGB array:
test('SetRGBColor by name', xd(0,0,255), 'blue');
test('SetRGBColor by name', xd(250,128,114), 'salmon');
test('SetRGBColor by name', xd(190,190,190), 'gray');

# ======== End of test cases and error reporting ==========

echo basename(__FILE__)
    . " results: $n_tests test cases, $n_pass pass, $n_fail fail\n";
if ($n_fail > 0) exit(1);
# PHPlot test suite requires falling off the end, not exit, on success.
