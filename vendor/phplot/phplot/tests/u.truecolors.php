<?php

# Unit tests for color functions - truecolor version
# This doesn't actually output a graph. It checks internal functions.

# This is an extension to u.colors which includes truecolor/alpha tests.

# Note: PHPlot-5.1.1, where truecolor images first appeared, acted
# differently for truecolor or base class objects. With base class objects,
# SetRGBColor() returned a 3 element array(r,g,b) and ignored any alpha value.
# Starting a few CVS revs after 5.1.1, SetRGBColor() will return a 4
# element array for both base and truecolor class, and does honor an alpha
# value if provided even for the base class.
# The test below automatically detects the situation and adapts to it.

require_once 'phplot.php';
require_once 'phplot_truecolor.php';
require_once 'usupport.php';

# True to report test cases and all results:
$test_verbose = False;

# Global test case counters:
$n_tests = 0;
$n_pass = 0;
$n_fail = 0;

# ====== Test functions ======

# Initialization:
# Creates the global PHPlot objects, and determines which form of
# SetRGBColor() exists: older, returns RGB, or newer, returns RGBA.
function test_init()
{
    global $returns_alpha, $p, $q, $test_verbose;

    $p = new Phplot\Phplot\phplot_truecolor(); // Global
    $q = new Phplot\Phplot\phplot(); // Global

    # Determine if SetRGBColor returns a 3 or 4 element array for the base
    # class, and keeps the alpha value:
    $test_array = $q->SetRGBColor(array(10, 10, 10, 10));
    if (count($test_array) == 3) {
        $style = "3 element arrays (old style)";
        $returns_alpha = False;
    } elseif (count($test_array) == 4 && $test_array[3] != 0) {
        $style = "4 element arrays (new style, with alpha)";
        $returns_alpha = True;
    } else {
        fwrite(STDERR, "u.colors: Unexpected return style from SetRGBArray\n");
        exit(1);
    }
    if ($test_verbose) echo "Checking SetRGBColor return: $style\n";
}

# Return a 3- or 4- element array for testing, depending on the flag which
# is set based on version of SetRGBColor():
# Note: This is used only when testing the base class, not truecolor class.
function xd($r, $g, $b, $a = 0)
{
    global $returns_alpha;
    if ($returns_alpha) return array($r, $g, $b, $a);
    return array($r, $g, $b);
}

# Execute one test case.
#  $name : Name or title of the test case
#  $expected : Expected result, 4 element array RGBA
#  $p : PHPlot or PHPlot_truecolor object (one of global $p or $q)
#  $arg : Argument to SetRGBColor
#  $alpha : Optional alpha arg to SetRGBColor.
# Increments the test, pass, and fail counters based on the results.
function test($name, $expected, $p, $arg, $alpha = NULL)
{
    global $test_verbose, $n_tests, $n_pass, $n_fail;

    $n_tests++;
    $title = "Test case $n_tests: $name";
    if ($test_verbose) echo "$title\n";

    $error = '';
    if (isset($alpha))
        $result = $p->SetRGBColor($arg, $alpha);
    else
        $result = $p->SetRGBColor($arg);

    if (expect_equal($expected, $result, $title, $error)) {
        $n_pass++;
    } else {
        $n_fail++;
        echo "$error\n";
    }
}

# ===== Test Cases =====

test_init();

# ===========================================================

if ($test_verbose) echo "Testing base SetRGBColor forms...\n";

# These duplicate tests in u.colors but with a 4th parameter (alpha) = 0
# in the return value.
test('SetRGBColor empty string',
     array(0,0,0,0), $p, '');

test('SetRGBColor #rrggbb black',
     array(0,0,0,0), $p, '#000000');

test('SetRGBColor #rrggbb white',
     array(255,255,255,0), $p, '#ffffff');

test('SetRGBColor #rrggbb white upper case',
     array(255,255,255,0), $p, '#FFFFFF');

test('SetRGBColor array',
     array(20,30,40,0), $p, array(20, 30, 40));

test('SetRGBColor black array',
     array(0,0,0,0), $p, array(0 ,0 ,0));

# Named colors from the RGB array:
test('SetRGBColor by name',
     array(0,0,255,0), $p, 'blue');
test('SetRGBColor by name',
     array(250,128,114,0), $p, 'salmon');
test('SetRGBColor by name',
     array(190,190,190,0), $p, 'gray');

# ===========================================================

if ($test_verbose) echo "Testing extended SetRGBColor forms...\n";

# Alpha specified as separate argument, color spec without alpha:
test('SetRGBColor by name with alpha argument',
     array(250,128,114,20), $p, 'salmon', 20);
test('SetRGBColor #rrggbb with alpha argument',
     array(204,153,51,40), $p, '#CC9933', 40);
test('SetRGBColor array with alpha argument',
     array(100,50,150,40), $p, array(100,50,150), 40);

# Alpha included in color spec:
test('SetRGBColor by name:alpha',
     array(250,128,114,30), $p, 'salmon:30');
test('SetRGBColor #rrggbbaa',
     array(204,153,51,127), $p, '#CC99337f');
test('Test: SetRGBColor array including alpha',
     array(100,50,150,40), $p, array(100,50,150,40));

# Alpha included in color spec, default alpha ignored:
test('SetRGBColor by name:alpha w/ignored alpha arg',
     array(250,128,114,40), $p, 'salmon:40', 30);
test('SetRGBColor #rrggbbaa w/ignored alpha arg',
     array(204,153,51,32), $p, '#CC993320', 99);
test('SetRGBColor array including alpha w/ignored alpha arg',
     array(100,50,150,80), $p, array(100,50,150,80), 99);
test('SetRGBColor array including alpha=0 w/ignored alpha arg',
     array(100,50,150,0), $p, array(100,50,150,0), 99);

# ===========================================================

if ($test_verbose) echo "Testing other cases...\n";
# $alpha component and $alpha arg for PHPlot (base) objects:
test('Base class SetRGBColor with alpha in color spec',
     xd(255, 0, 0, 50), $q, array(255, 0, 0, 50));
test('TC class SetRGBColor uses alpha in color spec',
     array(255, 0, 0, 50), $p, array(255, 0, 0, 50));
test('Base class SetRGBColor with default alpha argument',
     xd(255, 0, 0, 50), $q, array(255, 0, 0), 50);
test('TC class SetRGBColor uses alpha in color spec',
     array(255, 0, 0, 50), $p, array(255, 0, 0, 50));

# ===========================================================

if ($test_verbose) echo "Testing alpha spec in color map...\n";
$my_colors = array(
  'red'       => array(255, 0, 0),      // Plain color
  'red:40'    => array(255, 0, 0, 50),  // Color with alpha, note 40 != 50
  'clearblue' => array(0, 0, 255, 88),  // Color with alpha
);
$p->SetRGBArray($my_colors);
$q->SetRGBArray($my_colors);

# Try each of the 3 color names. Note red:40 has alpha=50.
test('Color map color w/o alpha, TC class gets alpha=0',
     array(255, 0, 0, 0), $p, 'red');
test('Color map color w/o alpha, base class',
     xd(255, 0, 0, 0),    $q, 'red');
test('Color map color w/alpha in map, TC class uses that alpha',
     array(255, 0, 0, 50), $p, 'red:40');
test('Color map color w/alpha in map, base class',
     xd(255, 0, 0, 50),     $q, 'red:40');
test('Color map color w/alpha in map, TC class uses that alpha',
     array(0, 0, 255, 88), $p, 'clearblue');
test('Color map color w/alpha in map, base class',
     xd(0, 0, 255, 88),     $q, 'clearblue');

# Try with alpha default:
test('map color w/o alpha, alpha default, TC class uses it',
     array(255, 0, 0,30), $p, 'red', 30);
test('map color w/o alpha, alpha default, base class',
     xd(255, 0, 0, 30),    $q, 'red', 30);
test('map color w/alpha, alpha default, TC class ignores default',
     array(255, 0, 0, 50), $p, 'red:40', 30);
test('map color w/alpha, alpha default, base class',
     xd(255, 0, 0, 50),     $q, 'red:40', 30);
test('map color w/alpha, alpha default, TC class ignores default',
     array(0, 0, 255, 88), $p, 'clearblue', 30);
test('map color w/alpha, alpha default, base class',
     xd(0, 0, 255, 88),     $q, 'clearblue', 30);

# Try with alpha appended to color name:
test('map color w/o alpha, using :alpha, TC class uses it',
     array(255, 0, 0,10), $p, 'red:10');
test('map color w/o alpha, using :alpha, base class',
     xd(255, 0, 0, 10),    $q, 'red:10');
test('map color w/alpha, using :alpha, TC class uses :alpha',
     array(255, 0, 0, 10), $p, 'red:40:10');
test('map color w/alpha, using :alpha, base class',
     xd(255, 0, 0, 10),     $q, 'red:40:10');
test('map color w/alpha, using :alpha, TC class uses :alpha',
     array(0, 0, 255, 10), $p, 'clearblue:10');
test('map color w/alpha, using :alpha, base class',
     xd(0, 0, 255, 10),     $q, 'clearblue:10');

# ======== End of test cases and error reporting ==========

echo basename(__FILE__)
    . " results: $n_tests test cases, $n_pass pass, $n_fail fail\n";
if ($n_fail > 0) exit(1);
# PHPlot test suite requires falling off the end, not exit, on success.
