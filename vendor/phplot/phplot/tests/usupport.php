<?php

# Unit test support functions
# If global $test_debug is set to True, the compare functions are verbose.
# They write to stdout, not stderr. Stderr output indicates an error to
# the test driver. Stdout (for non-graphic tests) is displayed and ignored.

# Compare expected and actual results, identical equality test.
# This works on strings, numbers, arrays.
# If they match, return 1.
# If they don't match, append an error message to $result and return 0.
function expect_equal($expected, $actual, $message, &$result)
{
    global $test_debug;
    $ok = ($expected === $actual);
    if (!empty($test_debug))
        echo "$message: " . ($ok ? "OK" : "Error") . "\n";
    if (!$ok) {
      $result .= "ERROR: $message\n"
               . "  Expected: " . print_r($expected, True) . "\n"
               . "    Actual: " . print_r($actual, True) . "\n";
        return 0;
    }
    return 1;
}

# Check actual results against a regular expression.
# If the actual result matches the RE, return 1.
# If not, append an error message to $result and return 0.
#  $re does NOT include delimeters. They will be added, plus a
#  case insensitive modifier.
function expect_match($re, $actual, $message, &$result)
{
    global $test_debug;
    $ok = preg_match("\x01$re\x01i", $actual);
    if (!empty($test_debug))
        echo "$message: " . ($ok ? "OK" : "Error") . "\n";
    if (!$ok) {
      $result .= "ERROR: $message\n"
               . "   Expected results match pattern: $re\n"
               . "    Actual: " . print_r($actual, True) . "\n";
        return 0;
    }
    return 1;
}

# Compare expected and actual results, as real numbers, with "fuzz factor".
# If they match within +/- $epsilon, return 1.
# If they don't, append an error message to $result and return 0.
function expect_float($expected, $actual, $epsilon, $message, &$result)
{
    global $test_debug;
    $ok = ($expected - $epsilon <= $actual && $actual <= $expected + $epsilon);

    if (!empty($test_debug))
        echo "$message: " . ($ok ? "OK" : "Error") . "\n";
    if (!$ok) {
      $result .= "ERROR: $message\n"
               . "  Expected: $expected +/- $epsilon\n"
               . "    Actual: $actual\n";
        return 0;
    }
    return 1;
}
