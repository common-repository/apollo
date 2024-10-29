<?php

namespace Spaceinvoices;

class Taxes extends ApiResource {
  const path = "taxes";

  use ApiOperations\Find;
	use ApiOperations\Delete;
	use ApiOperations\Create;
	use ApiOperations\Edit;

  /**
   * @param string $taxId ID of Tax for which we are creting the Rate
   * @param object $taxRate	Rate of Tax
   *
   * @return object Returns object data of created TaxRate
  */
  public static function addANewRateToTax($taxId, $taxRate) {
    return parent::_POST("/taxes/".$taxId."/taxRates", $taxRate)->body;
  }
}
?>