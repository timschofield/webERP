<?php

namespace PGettext\Streams;

class StringReader implements StreamReaderInterface
{
  public $_pos;
  public $_str;

  function __construct($str='') {
    $this->_str = $str;
    $this->_pos = 0;
  }

  function read($bytes) {
    $data = substr($this->_str, $this->_pos, $bytes);
    $this->_pos += $bytes;
    if (strlen($this->_str)<$this->_pos)
      $this->_pos = strlen($this->_str);

    return $data;
  }

  function seekto($position) {
    $this->_pos = $position;
    if (strlen($this->_str) < $this->_pos)
      $this->_pos = strlen($this->_str);
    return $this->_pos;
  }

  function currentpos() {
    return $this->_pos;
  }

  function length() {
    return strlen($this->_str);
  }
}
