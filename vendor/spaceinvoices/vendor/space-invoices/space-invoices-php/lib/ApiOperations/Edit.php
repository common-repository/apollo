<?php
namespace Spaceinvoices\ApiOperations;

trait Edit
{
  /**
   * @param string $id ID of element to be edited
   * @param object $data
   *
   * @return object Returns data of edited element as object
  */
  public static function edit($id, $data) {
    return parent::_PUT("/".static::path."/".$id, $data)->body;
  }
}
?>