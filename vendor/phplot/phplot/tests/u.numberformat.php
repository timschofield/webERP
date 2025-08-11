<?php

# number_foramt unit test - change locale and format numbers.
# For Linux, the locale is changed by setting the environment variable
# LC_ALL. When PHPlot uses setlocale(LC_ALL, '') it loads the locale
# settings using the environment variable.
# This does not work on Windows, which loads the locale from the
# system regional settings, not the environment. On Windows we have
# to use setlocale() to set the locale, then tell PHPlot using a
# special flag to not override this by re-importing.
#
# Another trick is needed because Windows uses a non-breaking space 0xa0
# for thousands separator in some locales, and Linux uses a regular space.
#
# Locales use the .utf8 suffix on non-Windows. Leaving that off works on
# Slackware, but fails on Xubuntu. Slackware works with both. Xubuntu
# doesn't seem to have any non-English locales by default anyway, so the others
# fail regardless.
# One English, non-US locale with different separators (en_DK) was added to
# give Xubuntu something to work with.

/// @todo skip this test if the required locales are not available

require_once 'phplot.php';
require_once 'usupport.php';
require_once 'testclass.php'; // For access to protected methods

# True to report test cases and all results:
$test_verbose = True;

# Global test case counters:
$n_tests = 0;
$n_pass = 0;
$n_fail = 0;

# ====== Test Data ======

# The number to format:
define('R', 1234567.89012);
# The number of decimal places to test up to:
define('ND', 4);

# Test case data:
# Each element is an array describing a test with this data:
#    x_locale : Name of locale under *ix
#    win_locale : Name of locale under Windows (Empty to skip)
#    expected : Expected results, with newline after each result.
$tests = array(

  # Locale "C" has no thousands separator:
  array(
    'x_locale' => 'C',
    'win_locale' => 'C',
    'expected' => '1234568
1234567.9
1234567.89
1234567.890
1234567.8901
'),


  # Locale "en_US" uses , and .
  array(
    'x_locale' => 'en_US.utf8',
    'win_locale' => 'English_United States',
    'expected' => '1,234,568
1,234,567.9
1,234,567.89
1,234,567.890
1,234,567.8901
'),

  # Locale fr_CA uses space and ,
  array(
    'x_locale' => 'fr_CA.utf8',
    'win_locale' => 'French_Canada',
    'expected' => '1 234 568
1 234 567,9
1 234 567,89
1 234 567,890
1 234 567,8901
'),

  # Locale de_DE uses . and ,
  array(
    'x_locale' => 'de_DE.utf8',
    'win_locale' => 'German_Germany',
    'expected' => '1.234.568
1.234.567,9
1.234.567,89
1.234.567,890
1.234.567,8901
'),

  # Locale en_DK uses . and ,
  # This was added because my Xubuntu has only English locales, so the
  # 2 above fail, but this one works and is different from US separators.
  # Skip this case on Windows - can't find equivalent locale.
  array(
    'x_locale' => 'en_DK.utf8',
    'win_locale' => '',
    'expected' => '1.234.568
1.234.567,9
1.234.567,89
1.234.567,890
1.234.567,8901
'),

);


# ====== Test functions ======

function run_test($value, $decimals, $decpt = NULL, $thoumark = NULL)
{
  $result = '';
  $p = new phplot_test();
  if (PHP_OS == "WINNT") {
    $p->locale_override = True;
  }

  if (isset($decpt)) {
    $p->SetNumberFormat($decpt, $thoumark);
  }
  for ($i = 0; $i <= $decimals; $i++) {
    $result .= $p->test_number_format($value, $i) . "\n";
  }
  return $result;
}

# Test: format with locale
function test1($locale_name, $expected_result)
{
    global $test_verbose, $n_tests, $n_pass, $n_fail;

    $n_tests++;
    $title = "number_format (case $n_tests) locale=$locale_name";
    if ($test_verbose) echo "$title\n";

    if (PHP_OS == "WINNT") {
        setlocale(LC_ALL, $locale_name);
    } else {
        putenv("LC_ALL=$locale_name");
    }

    # Run test, but replace non-breaking spaces with spaces in the result:
    $result = str_replace("\xa0", ' ', run_test(R, ND));
    $error = '';
    if (expect_equal($expected_result, $result, $title, $error)) {
        $n_pass++;
    } else {
        $n_fail++;
        echo "$error\n";
    }
}

# Test: Separator override
function test2($dec_sep, $thou_sep, $expected_result)
{
    global $test_verbose, $n_tests, $n_pass, $n_fail;

    $n_tests++;
    $title = "number_format (case $n_tests) Separator Overrride";
    if ($test_verbose) echo "$title\n";

    $result = run_test(R, ND, $dec_sep, $thou_sep);
    $error = '';
    if (expect_equal($expected_result, $result, $title, $error)) {
        $n_pass++;
    } else {
        $n_fail++;
        echo "$error\n";
    }
}

# ===== Test Cases =====

# Test override separators: (Run first, before setting LC_ALL)
test2('=', '+', '1+234+568
1+234+567=9
1+234+567=89
1+234+567=890
1+234+567=8901
');

# Select locale name per O/S:
if (PHP_OS == "WINNT") {
  $locale_index = "win_locale";
} else {
  $locale_index = "x_locale";
}
# Run through the test array:
foreach ($tests as $d) {
    if (($locale_name = $d[$locale_index]) != '') {
        test1($locale_name, $d['expected']);
    }
}

# ======== End of test cases and error reporting ==========
echo "numberformat results: $n_tests test cases, $n_pass pass, $n_fail fail\n";
if ($n_fail > 0) exit(1);
# PHPlot test suite requires falling off the end, not exit, on success.
