<?php

namespace Spaceinvoices;

class Clients extends ApiResource {
  const path = "clients";

  use ApiOperations\Find;
	use ApiOperations\Create;
	use ApiOperations\Delete;
	use ApiOperations\Edit;
	use ApiOperations\GetById;

  /**
   * @param string $organizationId ID of Organization
   * @param string $searchTerm String term to search in Client properties
   *
   * @return object Returns list of Clients
  */
  public static function search($organizationId, $searchTerm) {
    return parent::_GET("/organizations/".$organizationId."/search-clients?term=".$searchTerm)->body;
  }

}
?>