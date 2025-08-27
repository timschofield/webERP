<?php

namespace PGettext\Streams;

/**
 * Preloads the entire file in memory first, then creates a StringReader over it
 */
class CachedFileReader extends StringReader
{
  public $error = 0; // public variable that holds error code (0 if no error)

  /**
   * @param string $filename
   */
  function __construct($filename) {
    if (file_exists($filename)) {

      $length = filesize($filename);
      $fd = fopen($filename,'rb');

      if ($fd) {
        $this->_str = fread($fd, $length);
        fclose($fd);
      } else {
        $this->error = 3; // Cannot read file, probably permissions
      }
    } else {
      $this->error = 2; // File doesn't exist
    }
  }
}
