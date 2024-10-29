<?php

namespace Spaceinvoices;

class Items extends ApiResource {
  const path = "items";

  use ApiOperations\Find;
	use ApiOperations\Create;
	use ApiOperations\Delete;
	use ApiOperations\Edit;

  /**
   * @param string $organizationId ID of Organization
   * @param string $searchTerm String term to search for in Item properties
   *
   * @return object Returns list of Items
  */
  public static function search($organizationId, $searchTerm) {
    return parent::_GET("/organizations/".$organizationId."/search-items?term=".$searchTerm)->body;
  }

}
?>