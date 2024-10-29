<?php
namespace Spaceinvoices\ApiOperations;

trait Create
{
  /**
   * @param string $organizationId ID of Organization
   * @param object $data
   *
   * @return object Returns data of created element as object
  */
  public static function create($organizationId, $data) {
    return parent::_POST("/organizations/".$organizationId."/".static::path, $data)->body;
  }

}
?>