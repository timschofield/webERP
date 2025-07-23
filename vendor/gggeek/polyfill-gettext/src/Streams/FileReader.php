<?php

namespace PGettext\Streams;

class FileReader implements StreamReaderInterface
{
  public $_pos;
  public $_fd;
  public $_length;
  public $error = 0; // public variable that holds error code (0 if no error)

  function __construct($filename) {
    if (file_exists($filename)) {

      $this->_length = filesize($filename);
      $this->_pos = 0;
      $this->_fd = fopen($filename,'rb');
      if (!$this->_fd) {
        $this->error = 3; // Cannot read file, probably permissions
      }
    } else {
      $this->error = 2; // File doesn't exist
    }
  }

  function read($bytes) {
    if ($bytes) {
      fseek($this->_fd, $this->_pos);

      // PHP 5.1.1 does not read more than 8192 bytes in one fread()
      // the discussions at PHP Bugs suggest it's the intended behaviour
      $data = '';
      while ($bytes > 0) {
        $chunk  = fread($this->_fd, $bytes);
        $data  .= $chunk;
        $bytes -= strlen($chunk);
      }
      $this->_pos = ftell($this->_fd);

      return $data;
    } else return '';
  }

  function seekto($position) {
    fseek($this->_fd, $position);
    $this->_pos = ftell($this->_fd);
    return $this->_pos;
  }

  function currentpos() {
    return $this->_pos;
  }

  function length() {
    return $this->_length;
  }

  function close() {
    fclose($this->_fd);
  }
}
