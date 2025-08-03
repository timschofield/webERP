<?php

namespace PGetText;

interface ReaderInterface
{
  /**
   * Plural version of gettext
   *
   * @param string $singular
   * @param string $plural
   * @param string $number
   * @return string translated plural form
   */
  public function ngettext($singular, $plural, $number);

  /**
   * @param string $context
   * @param string $singular
   * @param string $plural
   * @param string $number
   * @return string
   */
  public function npgettext($context, $singular, $plural, $number);

  /**
   * @param string $context
   * @param string $msgid
   * @return string
   */
  public function pgettext($context, $msgid);

  /**
   * Translates a string
   *
   * @param string $string string to be translated
   * @return string translated string (or original, if not found)
   */
  public function translate($string);
}
