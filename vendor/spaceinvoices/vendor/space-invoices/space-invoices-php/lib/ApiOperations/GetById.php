<?php
namespace Spaceinvoices\ApiOperations;

trait GetById
{
  /**
   * @param string $id ID of element to be retrived
   *
   * @return object Returns data of element as object
  */
  public static function getById($id) {
    return parent::_GET("/".static::path."/".$id)->body;
  }
}
?>