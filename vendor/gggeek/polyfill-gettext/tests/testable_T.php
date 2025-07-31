<?php

class testable_T  extends PGettext\T
{
  public static function resetDomains() {
    static::$text_domains = array();
    static::$current_domain = 'messages';
  }
}
