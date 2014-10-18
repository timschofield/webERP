<?php

/**
 * This file is part of the BarcodePack - PHP Barcode Library.
 * Copyright (c) 2011 Tomáš Horáček (http://www.barcodepack.com)
 * BarcodePack by Tomáš Horáček is licensed under
 * a Creative Commons Attribution-NoDerivs 3.0 Unported License.
 */



require_once 'class.barcode.php';

// Error codes
define('E_BAD_CHARS', 200);


/**
 * Linear Barcode
 * Parent class for all linear barcode types
 *
 * @author Tomáš Horáček <info@webpack.cz>
 * @package BarcodePack
 */
class linearBarcode extends barcode {

	/** @var array */
	protected $biteCode = array();

	/** @var int */
	protected $height = 100;

	/** @var int */
	protected $fontSize = 10;

	/**
	 * Quiet zone
	 * Multiple of module size
	 * @var int */
	protected $margin = 5;

	/**
	 * Constructor
	 *
	 * @param string $text
	 * @param int $modulesize
	 */
	public function __construct($text, $moduleSize=2, $allowedChars=null)
	{
		try {
			parent::__construct($text, $moduleSize);

			if($allowedChars) {
				$this->checkAllowedChars($text, $allowedChars);
			}

		} catch (Exception $e) {
			throw $e;
		}
	}


	/**
	 * Check Allowed Chars
	 *
	 * @param string $text
	 * @param array $alloweChars
	 * @return bool
	 */
	protected function checkAllowedChars($text, $allowedChars)
	{
		for($i=0; $i<strlen($text); $i++) {
			if(!in_array($text{$i}, $allowedChars)) {
				throw new Exception('Input text contains nonallowed characters.', E_BAD_CHARS);
				return false;
			}
		}
		return true;
	}



	/**
	 * Get Barcode Length
	 * @return int
	 */
	protected function getBarcodeLen() {
		$len = 0;
		foreach ($this->biteCode as $value) {
			$len += strlen($value);
		}
		return $len;
	}


	/**
	 * Draw
	 * Create image with barcode
	 *
	 * @param bool $showText
	 * @return image resource
	 */
	public function draw($showText=true)
	{
		// Image create
		$margin = $this->margin*$this->moduleSize;
		$im = ImageCreate($this->getBarcodeLen()*$this->moduleSize+(2*$margin),
				$this->height+$this->fontSize+(2*$margin));

		// Color set
		$white = Imagecolorallocate ($im,255,255,255);
		$black = Imagecolorallocate ($im,0,0,0);


		// Draw lines
		$pos = 0;
		foreach ($this->biteCode as $type => $values) {
			switch($type) {
				case 'DATA':
				case 'DATA2':
					// Data
					for($i=0;$i<strlen($values);$i++) {
						$color = (($values{$i})=='1') ? $black : $white;
						imagefilledrectangle($im, $pos*$this->moduleSize+$margin, $margin,
								($pos+1)*$this->moduleSize+$margin,
								$this->height-5*$this->moduleSize+$margin, $color);
						$pos++;
					}
					break;
				default:
					// Special chars
					// will be longer
					for($i=0;$i<strlen($values);$i++) {

						$color = (($values{$i})=='1') ? $black : $white;
						imagefilledrectangle($im, $pos*$this->moduleSize+$margin, $margin,
								($pos+1)*$this->moduleSize+$margin, $this->height+$margin,
								$color);
						$pos++;
					}

					break;
			}
		}

		// Text
		if($showText) {
			$textWidth = ImageFontWidth($this->fontSize)*strlen($this->text);
			imagestring ($im, $this->fontSize,
					$this->getBarcodeLen()*$this->moduleSize/2-$textWidth/2+$margin,
					$this->height-$this->fontSize/2+$margin, $this->text, $black);
		}

		return $im;
	}


	/**
	 * Raw Data
	 * Returns data in text representation
	 * Black module is represented as 1 and white module as 0
	 *
	 * @return string $output
	 */
	public function rawData()
	{
		$ret = '';
		foreach ($this->biteCode as $value) {
			$ret .= $value;
		}
		return $ret;
	}


}
