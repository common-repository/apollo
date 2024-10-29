<?php

namespace Spaceinvoices;

class Currencies extends ApiResource {
	const path = "currencies";

  /**
   * @return object Returns list of Currencies
  */
  public static function find() {
    return parent::_GET("/".static::path)->body;
  }

}
?>