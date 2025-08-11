<?php

# PHPlot Test Suite Support - create a valid data array
/*
   make_data_array():

   Given a data type and plot type, and some numbers, this function will
return a valid data array with pseudo-random data.

   $plot_type : Any valid PHPlot plot type.
   $data_type : Any valid PHPlot data type for the given plot_type.
   $nx : Number of independent variable values (usually X)
   $ny : Number of dependent variable values (usually Y) per indepenent
         variable value. Note: Some plot types restrict this.
   $max : The maximum value for each data value, which will be a random
          integer between 0 and $max inclusive. The values are random,
          but repeatable.
   Returns a data array, or False if the parameters are invalid.

Notes:

  This does not detect every invalid combination. PHPlot will detect more.

  For data-data, this uses X = 0.5, 1.5, etc rather than the expected X=0, 1,
..., just like PHPlot does with text-data. This lets the tests set the plot
area consistently.

*/
function make_data_array($plot_type, $data_type, $nx, $ny, $max)
{

    // Fixed seed for repeatable results, but vary per plot type:
    mt_srand(strlen($plot_type) + strlen($data_type) + $nx + $ny + $max);

    // These plot types have special data requirements:
    if ($plot_type == 'ohlc' || $plot_type == 'candlesticks'
                             || $plot_type == 'candlesticks2') {
        if ($ny != 1) return FALSE;
        return make_data_array_ohlc($data_type, $nx, $max);
    }

    $data = array();

    switch ($data_type) {

    case 'data-data-yx':      //   same as data-data
    case 'data-data':         //    label, x, y1, y2, ... yn
        for ($i = 0; $i < $nx; $i++) {
            $row = array(make_data_array_label($i), $i + 0.5); // Label and X
            for ($j = 0; $j < $ny; $j++)
                $row[] = mt_rand(0, $max); // Y value
            $data[] = $row;
        }
        break;

    case 'data-data-error':   //    label, x, {y1, e+1, e-1}, ... yn, e+n, e-n
        // Arbitrarily use 10% error range, but don't let error be 0.
        // Also clip Y+err to $max and Y-err to 0.
        $errmax = max(2, (int)($max / 10));
        for ($i = 0; $i < $nx; $i++) {
            $row = array(make_data_array_label($i), $i + 0.5); // Label and X
            for ($j = 0; $j < $ny; $j++) {

                $row[] = $yval  = mt_rand(0, $max); // Y value

                if ($yval == $max) $err_plus = 0;
                else $err_plus = mt_rand(1, min($errmax, $max - $yval));
                $row[] = $err_plus;  // +err

                if ($yval == 0) $err_minus = 0;
                else $err_minus = mt_rand(1, min($errmax, $yval));
                $row[] = $err_minus; // -err
            }
            $data[] = $row;
        }
        break;

    case 'text-data-single':  //   same as text-data with ny=1
        if ($ny != 1) return FALSE;
        // Fall throught to text-data case:
    case 'text-data':         //    label, y1, y2,... yn
    case 'text-data-yx':      //   same as text-data
        for ($i = 0; $i < $nx; $i++) {
            $row = array(make_data_array_label($i));
            for ($j = 0; $j < $ny; $j++)
                $row[] = mt_rand(0, $max); // Y value
            $data[] = $row;
        }
        break;

    case 'data-data-xyz':     //    label, X, {y1, z1}, ... yn, zn
        for ($i = 0; $i < $nx; $i++) {
            $row = array(make_data_array_label($i), $i + 0.5); // Label and X
            for ($j = 0; $j < $ny; $j++) {
                $row[] = mt_rand(0, $max); // Y value
                $row[] = mt_rand(0, $max); // Z value
            }
            $data[] = $row;
        }
        break;

    default:
        return FALSE;
    }

    return $data;
}


/*
  make_data_array_ohlc():

  Special case to make a valid OHLC data array.
  This is used by make_data_array when plot_type is an OHLC type.
  Method: Limits the total gain or loss to 25% each interval.
     Start with the first $open value as a random number between 0 and $max.
     For each X:
        $high = a random value between $open and min(1.25 * $open, $max)
        $low = a random value between max(0, 0.75 * $open) and min($open, $high)
        $close = a random value between $low and $high
        Set the next $open to this $close.
  Calculations are in integer cents, then stored as dollars (a/100).
*/

function make_data_array_ohlc($data_type, $nx, $max)
{
    $need_x = ($data_type == 'data-data');

    $max = (int)($max * 100); // Scale from dollars to cents.

    $data = array();
    $open = mt_rand(0, $max); // First open

    for ($i = 0; $i < $nx; $i++) {
        $high = mt_rand($open, (int)min(1.25 * $open, $max));
        $low = mt_rand((int)max(0, 0.75 * $open), min($open, $high));
        $close = mt_rand($low, $high);
        $row = array(make_data_array_label($i));
        if ($need_x) $row[] = $i + 0.5;
        $row[] = $open / 100;
        $row[] = $high / 100;
        $row[] = $low / 100;
        $row[] = $close / 100;
        $data[] = $row;
        $open = $close; // Next interval's open = this interval's close.
    }
    return $data;
}

/*
  Helper for make_data_array() and make_data_array_ohlc(): make a label.
    $n : An integer >= 0
   Returns a label for $n: A-Z, then BA BB BC ... (Note it doesn't use
   the more conventional AA AB AC... because it considers A=0.)
*/
function make_data_array_label($n)
{
    static $letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $result = "";
    while (1) {
        $c = $letters[$n % 26];
        $result = $c . $result;
        if ($n < 26) break;
        $n  = (int)($n / 26);
    }
    return $result;
}
