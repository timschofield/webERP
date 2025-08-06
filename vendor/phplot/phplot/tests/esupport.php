<?php
# Error test support routines


# Error handler - report the error and exit:
function test_catch_exit($errno, $errstr, $errfile, $errline)
{
  fwrite(STDERR, "Caught error ($errno): $errstr\n");
  fwrite(STDERR, "  File: $errfile Line: $errline\n");
  fwrite(STDERR, "  Exit on error\n");
  exit(1);
}

# Error handler - report the error and return.
function test_catch_return($errno, $errstr, $errfile, $errline)
{
  fwrite(STDERR, "Caught error ($errno): $errstr\n");
  fwrite(STDERR, "  File: $errfile Line: $errline\n");
  fwrite(STDERR, "  Returning after error\n");
}
