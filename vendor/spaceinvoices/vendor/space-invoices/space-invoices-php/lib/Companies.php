<?php

namespace Spaceinvoices;

class Companies extends ApiResource {
  const path = "companies";

  /**
   * @param object $filter Object containing query filters
   *
   * @return object Returns list of Companies
  */
  public static function find($filter = array()) {
    return parent::_GET("/".static::path, $filter)->body;
  }

  /**
   * @param string $searchTerm String term to search in Companies properties
   * @param object $filter Object containing query filters
   *
   * @return object Returns list of Companies
  */
  public static function search($searchTerm, $filter = array()) {
    return parent::_GET("/".static::path."/search?term=".$searchTerm, $filter)->body;
  }

}
?>