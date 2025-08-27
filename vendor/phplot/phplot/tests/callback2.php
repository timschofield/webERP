<?php

# Testing phplot: Callback with method, extended class.
require_once 'phplot.php';

class my_PHPlot extends Phplot\Phplot\phplot
{
  function __construct($width=600, $height=400, $outfile=NULL, $infile=NULL)
  {
    parent::__construct($width, $height, $outfile, $infile);
  }

  function callback($img, $arg)
  {
    #fwrite(STDERR, "callback in object: arg=" . print_r($arg, True) . "\nimg=" . print_r($img, True) . "\n");
    # fwrite(STDERR, print_r($this, True));
    #fwrite(STDERR, "Plot area: ({$this->plot_area[0]}, {$this->plot_area[1]}) :");
    #fwrite(STDERR, " ({$this->plot_area[2]}, {$this->plot_area[2]})\n");

    # Draw an X across the plot area.
    $red = ImageColorResolve($img, 255, 0, 0);
    ImageLine($img, $this->plot_area[0], $this->plot_area[1],
                    $this->plot_area[2], $this->plot_area[3], $red);
    ImageLine($img, $this->plot_area[0], $this->plot_area[3],
                    $this->plot_area[2], $this->plot_area[1], $red);
  }
}

$data = array(
  array('',  0,  4),
  array('',  1,  0),
  array('',  2,  6),
  array('',  3,  2),
);

$p = new my_PHPlot(400,300);
# PHP4 requires & to work. PHP5 can use either, but without & is preferred.
if (substr(PHP_VERSION, 0, 2) == '4.') {
  $p->SetCallback('draw_titles', array(&$p, 'callback'), 1234);
} else {
  $p->SetCallback('draw_titles', array($p, 'callback'), 1234);
}
$p->SetTitle('Callback Test');
$p->SetXTitle('X Axis');
$p->SetYTitle('Y Axis');
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');
$p->SetXTickIncrement(1.0);
$p->SetYTickIncrement(1.0);
$p->SetPlotType('lines');
$p->DrawGraph();
