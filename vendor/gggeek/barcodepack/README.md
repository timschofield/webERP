# BarcodePack - PHP Barcode Library

Code originally from Tomáš Horáček, at https://sourceforge.net/projects/barcodepack/

## Introduction

BarcodePack is PHP library which allows you to generate the most common types of barcodes.

BarcodePack support the following types of barcodes:

* QR Code
* EAN
* UPC
* Code 128
* Interleaved Code 2 of 5
* Standard Code 2 of 5

## Requirements

The php GD extension

## Installation

Use Composer to install this library

## Usage

Example:

```php
<?php

// Include the QR Code classes via Composer
include 'vendor/autoload.php';

// Make new instance of QR Code class
$qr = new \BarcodePack\qrCode('Hello World!', 5);

// We will outputting a PNG image
header('Content-type: image/png');

// Call draw method and output image
imagepng(($qr->draw());
```

## License

Creative Commons Attribution-NoDerivs 3.0 Unported License - see https://creativecommons.org/licenses/by-nd/3.0/
