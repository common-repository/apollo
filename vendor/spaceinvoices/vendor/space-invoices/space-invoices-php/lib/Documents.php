<?php

namespace Spaceinvoices;

class Documents extends ApiResource {
	const path = "documents";

	use ApiOperations\Find;
	use ApiOperations\Create;
	use ApiOperations\Delete;
	use ApiOperations\Edit;
	use ApiOperations\GetById;

  /**
   * @param string $documentId ID of Document
   * @param object $data
	 * @param string $lang Language of Document
   *
   * @return object
  */
  public static function send($documentId, $data, $lang = false) {
		if ($lang) {
			return parent::_POST("/".static::path."/".$documentId."/send?l=".$lang, $data)->body;
		}
    return parent::_POST("/".static::path."/".$documentId."/send", $data)->body;
	}

	/**
   * @param string $documentId ID of Document
	 * @param string $lang Language of PDF Document
   *
   * @return \Httpful\Response Returns object data of PDF of Document
  */

	public static function getPdf($documentId, $lang = false) {
    if ($lang) {
      return parent::_PDF("/".static::path."/".$documentId."/pdf?l=".$lang)->body;
    }
    return parent::_PDF("/".static::path."/".$documentId."/pdf")->body;
  }
}
?>