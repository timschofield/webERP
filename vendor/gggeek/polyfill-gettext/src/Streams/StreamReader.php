<?php

namespace PGettext\Streams;

/**
 * Simple class to wrap file streams, string streams, etc.
 * Seek is essential, and it should be byte stream
 *
 * @todo implement - this seems broken atm (it was most likely created as abstract base class)
 */
class StreamReader implements StreamReaderInterface
{
  // should return a string [FIXME: perhaps return array of bytes?]
  function read($bytes) {
    return false;
  }

  // should return new position
  function seekto($position) {
    return false;
  }

  // returns current position
  function currentpos() {
    return false;
  }

  // returns length of entire stream (limit for seekto()s)
  function length() {
    return false;
  }
}
