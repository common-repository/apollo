<?php

namespace Spaceinvoices;

class Payments extends ApiResource {
  const path = "payments";

  use ApiOperations\Find;
	use ApiOperations\Delete;
	use ApiOperations\Edit;

  /**
   * @param string $documentId ID of Document we are creating Payment for
   * @param object $data
   *
   * @return object Returns object data of created Payment
  */
  public static function create($documentId, $data) {
    return parent::_POST("/documents/".$documentId."/".static::path, $data)->body;
  }

}
?>