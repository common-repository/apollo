<?php
namespace Spaceinvoices\ApiOperations;

trait Delete
{
  /**
   * @param string $id ID of element to be deleted
   *
   * @return object Returns number of deleted elements
  */
  public static function delete($id) {
    return parent::_DELETE("/".static::path."/".$id)->body;
  }
}
?>