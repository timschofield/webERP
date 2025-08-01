<?php

/**
 * Makes public the parent's protected methods which we want to be able to call from tests
 */
class testable_gettext_reader extends PGettext\gettext_reader
{
  public function extract_plural_forms_header_from_po_header($header) {
    return parent::extract_plural_forms_header_from_po_header($header);
  }

  public function select_string($n) {
    return parent::select_string($n);
  }
}
