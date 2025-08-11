<?php

# PHPlot Unit test for SetDataColors, SetErrorBarColors, SetDataBorderColors
/* Specification:

  SetDataColors(arg)       data_colors is=       Result:
  SetErrorBarColors(arg)   error_bar_colors=
  SetDataBorderColors(arg) data_border_colors=
  ------------------------ --------------------  -----------------
  arg= NULL or missing      array                Do nothing.
  arg= NULL or missing      not set              Use default map
  arg= '' or False          -                    Use default map
  arg= word                 -                    Use array(arg)
  arg= array                -                    Use arg as array

 For PHPlot <= 5.0.7, "default map" was a special map of 4 colors for
     data_colors, and an array of just black for error_bar_colors.
 For PHPlot > 5.0.7, "default map" is the full default map, for both
     data_colors and error_bar_colors.
 In all cases, for data_border_colors, default map is just black.

*/

require_once 'phplot.php';

// Extend PHPlot class to allow access to protected variable(s):
class PHPlot_pv extends Phplot\Phplot\phplot {
    public function HAS_default_colors()
    {
        return isset($this->default_colors);
    }
    public function GET_data_colors()
    {
        if (isset($this->data_colors)) return $this->data_colors;
        return NULL;
    }
    public function GET_data_border_colors()
    {
        if (isset($this->data_border_colors)) return $this->data_border_colors;
        return NULL;
    }
    public function GET_error_bar_colors()
    {
        if (isset($this->error_bar_colors)) return $this->error_bar_colors;
        return NULL;
    }
}

# True to report test cases and all results:
$test_verbose = False;
# Set this to true (vs undefined) to report color map details.
#$test_debug = True;

# Global test case counters:
$n_tests = 0;
$n_pass = 0;
$n_fail = 0;

# ====== Test Data ======

$c6 = array('red', 'green', 'blue', 'cyan', 'magenta', 'yellow');

# ====== Test functions ======

# Describe a color array. If $test_debug is True, echo information.
# Returns -1 if null, -2 if scalar, or N>=0 if array of N elements.
# Starting PHPlot > 5.1.2 (colors rewrite), the color arrays are no longer
# arrays of names but arrays of array(r,g,b,a)
function describe($name, $v)
{
    global $test_debug;

    $s = "  $name: ";
    if (!isset($v)) {
        $result = -1;
        $s .= ' NULL';
    } elseif (!is_array($v)) {
        $s .= ' NOT Array!';
        $result = -2;
    } else {
        $result = count($v);
        if (!is_array($v[0])) {
            // Old way : array of color names
            $s .= " Array of $result colors = " . implode(', ', $v);
        } else {
            $s .= " Array of $result colors =";
            // New way: array of RGBA arrays
            foreach ($v as $color_entry) {
                $s .= ' (' . implode(', ', $color_entry) . ')';
            }
        }
    }

    if (!empty($test_debug)) echo $s . "\n";
    return $result;
}

# Check color arrays and compare with expected.
# Increments the test, pass, and fail counters based on the results.
function test($name, $p, $e_data_colors, $e_border_colors, $e_error_colors)
{
    global $test_verbose, $n_tests, $n_pass, $n_fail;

    $n_tests++;
    if ($test_verbose) echo "Test case $n_tests: $name\n";

   $error = '';

   if (($n = describe('Data Colors', $p->GET_data_colors()))
                    != $e_data_colors)
       $error .= "  Data Colors status $n expecting $e_data_colors\n";
   if (($n = describe('Data Border Colors', $p->GET_data_border_colors()))
                    != $e_border_colors)
       $error .= "  Data Border Colors status $n expecting $e_border_colors\n";
   if (($n = describe('Error Bar Colors', $p->GET_error_bar_colors()))
                    != $e_error_colors)
       $error .= "  Error Bar Colors status $n expecting $e_error_colors\n";


    if (empty($error)) {
        $n_pass++;
    } else {
        $n_fail++;
        echo "Failed test case $n_tests: $name\n$error";
    }
}

# ===== Test Cases =====

$p = new phplot_pv();
# Use an internal variable to determine the PHPlot version. After this variable
# was introduced, the color maps expanded.
if ($p->HAS_default_colors()) {
    $dcol = 16;
    $ecol = 16;
    $bcol = 1;
} else {
    $dcol = 8;
    $ecol = 8;
    $bcol = 1;
}
test('new phplot object',
     $p, $dcol, $bcol, $ecol);

$p->SetDataColors('red');
$p->SetDataBorderColors('blue');
$p->SetErrorBarColors('green');
test('Then set color arrays to single word',
     $p, 1, 1, 1);

$p = new phplot_pv();
$p->SetDataColors($c6);
$p->SetDataBorderColors($c6);
$p->SetErrorBarColors($c6);
test('New object, set color arrays to array of 6',
     $p, 6, 6, 6);

$p->SetDataColors();
$p->SetDataBorderColors();
$p->SetErrorBarColors();
test('Then use NULL, should be ignored',
     $p, 6, 6, 6);

$p->SetDataColors('');
$p->SetDataBorderColors('');
$p->SetErrorBarColors('');
test('Then set color arrays to default, using empty string',
     $p, $dcol, $bcol, $ecol);

$p = new phplot_pv();
$p->SetDataColors($c6);
$p->SetDataColors(False);
$p->SetDataBorderColors(False);
$p->SetErrorBarColors(False);
test('New object, using False also sets color arrays to default',
     $p, $dcol, $bcol, $ecol);

# ======== End of test cases and error reporting ==========

echo "setcolors results: $n_tests test cases, $n_pass pass, $n_fail fail\n";
if ($n_fail > 0) exit(1);
# PHPlot test suite requires falling off the end, not exit, on success.
