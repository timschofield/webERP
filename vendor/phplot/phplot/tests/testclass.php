<?php
# PHPlot test support - superclass
# This script defines 2 superclasses of PHPlot which are for testing.
#
# class PHPlot_test : extends PHPlot and allows access to some protected
# methods which are needed by tests (mostly unit tests). The
# methods are called with a test_ prefix.
# Note: Methods which were 'recently' added are called conditionally.
#
# class PHPlot_test2 : The same as PHPlot_test except it replaces
# PrintError to (1) not produce error images, just messages; (2) return
# after error; and (3) if $p->hide_error is set it won't output an
# error message at all. This class can be used in unit tests that need
# to detect and continue after fatal errors.

// Extend PHPlot to access protected methods needed by tests.
class PHPlot_test extends Phplot\Phplot\phplot
{
    // GetDefaultTTFont()
    function test_GetDefaultTTFont()
    {
        if (method_exists($this, 'GetDefaultTTFont'))
            return $this->GetDefaultTTFont();
        return '';
    }

    // CheckDataArray()
    function test_CheckDataArray()
    {
        if (method_exists($this, 'CheckDataArray')) $this->CheckDataArray();
    }

    // FindDataLimits()
    function test_FindDataLimits()
    {
        return $this->FindDataLimits();
    }

    // number_format()
    function test_number_format() // Args vary, so use array call
    {
        $argv = func_get_args();
        return call_user_func_array(array($this, 'number_format'), $argv);
    }
}

// Extend PHPlot_test to suppress error images (and even messages).
class PHPlot_test2 extends PHPlot_test
{
    public $hide_error = False; // True to silence printerror completely.
    function PrintError($message)
    {
        if (!$this->hide_error) fwrite(STDERR, "PHPlot ERROR: $message\n");
        return FALSE;
    }
}
