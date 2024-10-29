<?php

namespace Spaceinvoices;

class Accounts extends ApiResource {
	const path = "accounts";

	use ApiOperations\GetById;

  /**
   * @param object $data Object containing Email and password
   *
   * @return object Returns access token and user ID
  */
  public static function login($data) {
    return parent::_POST("/".static::path."/login", $data)->body;
  }

  /**
   * @param object $data Object containing Email and password
   *
   * @return object Returns access token and user ID of created user
  */
  public static function create($data) {
    return parent::_POST("/".static::path, $data)->body;
  }

  /**
   * @param string $email
   *
   * @return object Returns object with filed "isUnique", which is true if email is free, false if taken
  */
  public static function isUnique($email) {
    return parent::_GET("/".static::path."/is-unique?email=".$email)->body;
  }

  /**
   * @return object Returns list of organizations
  */
  public static function listOrganizations() {
    return parent::_GET("/".static::path."/organizations")->body;
	}
}
?>