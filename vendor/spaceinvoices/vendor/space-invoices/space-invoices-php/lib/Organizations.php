<?php

namespace Spaceinvoices;

class Organizations extends ApiResource {
  const path = "organizations";

  use ApiOperations\GetById;

  /**
   * @param string $accountId ID of Account
   * @param object $data
   *
   * @return object Returns data of created Organization
  */
  public static function create($accountId, $data) {
    return parent::_POST("/accounts/".$accountId."/".static::path, $data)->body;
  }

  /**
   * @param string $accountId ID of Account
   *
   * @return object Returns list of Organizations
  */
  public static function find($accountId) {
    return parent::_GET("/accounts/".$accountId."/".static::path)->body;
  }

  /**
   * @param string $organizationId 	ID of Organization
   *
   * @return object Returns list of Business Premises
  */
  public static function getBusinessPremises($organizationId) {
    return parent::_GET("/".static::path."/".$organizationId."/businessPremises?filter[include]=electronicDevices")->body;
  }

   /**
   * @param string $organizationId 	ID of Organization
   *
   * @return object Returns list of units
  */
  public static function getUnits($organizationId) {
    return parent::_GET("/".static::path."/".$organizationId."/units")->body;
  }

}
?>