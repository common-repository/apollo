<?php
namespace Spaceinvoices\ApiOperations;

trait Find
{
  /**
   * @param string $organizationId ID of your Organization
   * @param object $queryParams Object containing query filters
   *
   * @return object Returns all data as object
  */
  public static function find($organizationId, $queryParams = array()) {
    return parent::_GET("/organizations/".$organizationId."/".static::path, $queryParams)->body;
  }

}
?>