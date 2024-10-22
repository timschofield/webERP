<?php

/*	$esc = '0x1B'; //ESC byte in hex notation
	$NewLine = '0x0A'; //LF byte in hex notation
	$CutPaper = $NewLine. $NewLine. $NewLine. '0x1D0x560x410x00';
	$InitPrinter = $esc . "@"; //Initializes the printer (ESC @)
	$Emphasized = $esc . '!'. '0x08'; //Emphasized 
	$DoubleHeight = $esc . '!'. '0x10'; //Emphasized 
	$EmphasizedDoubleHeight = $esc . '!'. '0x18'; //Emphasized + Double-height mode selected (ESC ! (16 + 8)) 24 dec => 18 hex
	$EmphasizedDoubleHeightDoubleWidth = $esc . '!'. '0x38'; //Emphasized + Double-height + Double-width mode selected (ESC ! (8 + 16 + 32)) 56 dec => 38 hex
	$CharacterFontA = $esc . '!'. '0x00'; //Character font A selected (ESC ! 0);
	$CharacterFontB = $esc . '!'. '0x01'; //Character font B selected (ESC ! 1);
	$LeftJustified = $esc . 'a'. '0x00'; 
	$CenteredJustified = $esc . 'a'. '0x01'; 
	$RightJustified = $esc . 'a'. '0x02'; 
*/
	$esc = '0x1B'; //ESC byte in hex notation
	$NewLine = '0x0A'; //LF byte in hex notation
	$CutPaper = $NewLine. $NewLine. $NewLine. '0x1D0x560x410x00';
	$InitPrinter = $esc . '0x40'; //Initializes the printer (ESC @)
	$Emphasized = $esc . '0x21'. '0x08'; //Emphasized 
	$DoubleHeight = $esc . '0x21'. '0x10'; //Emphasized 
	$EmphasizedDoubleHeight = $esc . '0x21'. '0x18'; //Emphasized + Double-height mode selected (ESC ! (16 + 8)) 24 dec => 18 hex
	$EmphasizedDoubleHeightDoubleWidth = $esc . '0x21'. '0x38'; //Emphasized + Double-height + Double-width mode selected (ESC ! (8 + 16 + 32)) 56 dec => 38 hex
	$CharacterFontA = $esc . '0x21'. '0x00'; //Character font A selected (ESC ! 0);
	$CharacterFontB = $esc . '0x21'. '0x01'; //Character font B selected (ESC ! 1);
	$LeftJustified = $esc . '0x61'. '0x00'; 
	$CenteredJustified = $esc . '0x61'. '0x01'; 
	$RightJustified = $esc . '0x61'. '0x02'; 

?>
