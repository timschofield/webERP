<?php

namespace PGettext\Streams;

interface StreamReaderInterface
{
  /**
   * @param int $bytes
   * @return string|false
   */
  function read($bytes);

  /**
   * Should return new position
   * @param int $position
   * @return int|false
   */
  function seekto($position);
}
