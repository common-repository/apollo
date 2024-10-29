<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'Apollo_invoice' ) ) {
	abstract class Apollo_invoice {

    //create Apollo invoice or estimate 
    public static function create( $order_id, $type = 'invoice' ) {
      $invoice_exsists = Apollo_invoice::getInvoice($order_id);
      $estimate_exsists = Apollo_invoice::getEstimate($order_id);

      // if document was already created, then just return it
      if ($type === 'invoice' && $invoice_exsists) {
          $invoice_exsists['exsists'] = true;
          return $invoice_exsists;
      } else if ($type === 'estimate' && $estimate_exsists) {
          $estimate_exsists['exsists'] = true;
          return $estimate_exsists;
      }

      $order = wc_get_order($order_id);

      $output = get_option('apollo_general_settings');
      $token = $output['apollo_token'];
      $organization_id = $output['apollo_organization-id'];
      $add_sku = (bool) get_option('apollo_general_settings')['apollo_add-sku'];

      $fiscalization = (bool) $output['apollo_enable-fiscalization'];
      $operator_tax_number = $output['apollo_operator-tax-number'];
      $category = $output['apollo_category-alias'];
      $lang = isset($output['apollo_document-lang'])
      ? $output['apollo_document-lang']
      : 'wp-default';

      $lang = $lang == 'wp-default' ? explode('_', get_locale())[0] : $lang;

      $premise = $output['apollo_premise-id'];
      $device = $output['apollo_device-id'];

      $unitID = $output['apollo_unit-id'] ?? '';

      if (!$order || !$output || !$token || !$organization_id) {
        return array("error" => "Apollo error: Missing data.");
      }

      Spaceinvoices\Spaceinvoices::setAccessToken($token);

      $SI_products_data = array(); // we will fill this with items that are in order
      $SI_total = 0;
      $order_total = 0;

      $unit = 'item';

      if($lang == 'si') {
        $unit = 'izdelek';
      }

      // Set the tax rates by rate ID in an array
      foreach ( $order->get_items('tax') as $tax_item ) {
        $tax_items_rates[$tax_item->get_rate_id()] = $tax_item->get_rate_percent();
      }
     
      // get data for each item
      foreach ($order->get_items() as $item_id => $item_data) {
        $product = $item_data->get_data();
        $product_variation_id = $item_data['variation_id'];

        if ($product_variation_id) { 
          $productData = wc_get_product($item_data['variation_id']);
        } else {
          $productData = wc_get_product($item_data['product_id']);
        }

        $without_tax = floatval($product['total']) / $product['quantity'];
        $tax_amount = floatval($product['total_tax']) / $product['quantity'];
        $tax_percent = 0;

        
        if($tax_amount > 0) {
          $taxes = $item_data->get_taxes() ?? [];
          $tax_rate_id = key($taxes['subtotal'] ?? $taxes['total'] ?? []) ?? false;
          $tax_rate_percentage = $tax_items_rates[$tax_rate_id] ?? false;
  
          $tax_percent = $tax_rate_percentage ?? ($tax_amount / $without_tax) * 100;
        }
        
        // Aggregate data for Apollo
        $product_data = array(
          'name'     => $product['name'],
          'unit'     => $unit,
          'quantity' => $product['quantity'],
          'price'    => $without_tax,
          '_documentItemTaxes' => array(array('rate' => $tax_percent))
        );

        $sku = $productData->get_sku();
        // error_log( print_r($sku, TRUE) );

        // add SKU code if item has one
        if($sku != '') {
          $product_data['SKU'] = $sku;
          
          // if 'Add SKU code to invoice' is enabled, add it to description
          if($add_sku) {
            $product_data['description'] = 'SKU: '.$sku;
          }
        }

        // if numbers don't match, we assume item was discounted (since there is no way of telling otherwise... atleast for now?)
        if (floatval($product['total']) < floatval($product['subtotal'])) {
          $product_data['discount'] = (1 - (floatval($product['total']) / floatval($product['subtotal']))) * 100;
          $product_data['price'] = floatval($product['subtotal']) / $product['quantity'];
        }

        $SI_products_data[] = $product_data;
      }

      $unit = 'shipping';

      if($lang == 'si') {
        $unit = 'dostava';
      }

      // get shippings data (can be multiple)
      foreach( $order->get_items('shipping') as $item_id => $item_shipping ) {
        $shipping = $item_shipping->get_data();

        $without_tax = floatval($shipping['total']);
        $tax_amount = floatval($shipping['total_tax']);

        $tax_percent = 0;

        if($tax_amount > 0) {
          $taxes = $item_shipping->get_taxes() ?? [];
          $tax_rate_id = key($taxes['subtotal'] ?? $taxes['total'] ?? []) ?? false;
          $tax_rate_percentage = $tax_items_rates[$tax_rate_id] ?? false;
  
          $tax_percent = $tax_rate_percentage ?? ($tax_amount / $without_tax) * 100;
        }

        $shipping_data = array(
          'name'     => $shipping['name'],
          'unit'     => $unit,
          'quantity' => 1,
          'price'    => $without_tax
        );
        if ($tax_percent != 0 && $tax_amount != 0 ) {
          $shipping_data['_documentItemTaxes'] = array(array('rate' => $tax_percent));
        }
        $SI_products_data[] = $shipping_data;
      }

      // get all extra fees
      foreach( $order->get_items('fee') as $item_id => $item_fee ) {
        $fee = $item_fee->get_data();

        $without_tax = floatval($fee['total']);
        $tax_amount = floatval($fee['total_tax']);

        $tax_percent = 0;

        if($tax_amount > 0) {
          $taxes = $item_fee->get_taxes() ?? [];
          $tax_rate_id = key($taxes['subtotal'] ?? $taxes['total'] ?? []) ?? false;
          $tax_rate_percentage = $tax_items_rates[$tax_rate_id] ?? false;
  
          $tax_percent = $tax_rate_percentage ?? ($tax_amount / $without_tax) * 100;
        }

        $unit = 'fee';

        if($lang == 'si') {
          $unit = 'strošek';
        }

        $fee_data = array(
          'name'     => $fee['name'],
          'unit'     => $unit,
          'quantity' => 1,
          'price'    => $without_tax
        );

        if ($tax_percent != 0 && $tax_amount != 0 ) {
          $fee_data['_documentItemTaxes'] = array(array('rate' => $tax_percent));
        }
        $SI_products_data[] = $fee_data;
      }

      // aggreagte order data for Apollo
      $order_data = array(
        "type" => $type,
        "currencyId" => $order->get_currency(),
        "_documentClient" => array(
          'name' 		=> $order->get_billing_company() !== '' ? $order->get_billing_company() : $order->get_billing_first_name(). ' ' .$order->get_billing_last_name(),
          'contact' => $order->get_billing_company() ? $order->get_billing_first_name(). ' ' .$order->get_billing_last_name() : '',
          'address' => $order->get_billing_address_1(),
          'address2'=> $order->get_billing_address_2(),
          'city'    => $order->get_billing_city(),
          'zip'			=> $order->get_billing_postcode(),
          'country' => $order->get_billing_country(),
          'email' 	=> $order->get_billing_email(),
          'phone' 	=> $order->get_billing_phone()
        ),
        "_documentItems" => $SI_products_data,
        "documentCategories" => array(array( 'categoryAlias' => isset($category) && $category != '' ? $category : 'woocommerce-shop' )),
        "expectedTotalWithTax" => $order->get_total()
      );

      if($unitID != '') {
          $order_data['unitId'] = $unitID;
      }

      // error_log( print_r($order_data, TRUE) );
      // if fiscalization is enabled and we making invoice, add the fiscalization data
      if ($fiscalization && $type === 'invoice') {
        $order_data['_furs'] = array(
          'businessPremiseId' => $premise ? $premise: '',
          'electronicDeviceId' => $device ? $device: '',
        );

        if ($operator_tax_number && $operator_tax_number !== '') {
          $order_data['_furs']['operatorTaxNumber'] = $operator_tax_number;
        } else {
          $order_data['_furs']['omitOperatorTaxNumber'] = true;
        }
      }
      
      $paymentType = "online";

      if($order->get_payment_method() === 'paypal')
      {
        $paymentType = 'paypal';
      } else if($order->get_payment_method() === 'bacs') {
        $paymentType = 'bank';
      }

      // if this is invoice and payment type IS NOT 'Cash on delivery', mark invoice as paid
      if ($type === 'invoice' && $order->get_payment_method() !== 'cod') {
        $order_data['payments'] = array(array(
          "type" => $paymentType,
          "note" => $order->get_payment_method_title()
        ));
      }

      $create = Spaceinvoices\Documents::create($organization_id, $order_data);

      if(isset($create->error)) {
        return array("error" => "Apollo error (".$create->error->statusCode."): ".$create->error->message);
      }

      $document_id = $create->id;
      $document_number = $create->number;

      $document_data = array(
        "type" => $type,
        "id" => $document_id,
        "number" => $document_number,
        "sent" => false
      );

      // save invoice/estimate data to order
      if ($type === 'invoice') {
        update_post_meta( $order_id, 'apollo_invoice_id', $document_id );
        update_post_meta( $order_id, 'apollo_invoice_number', $document_number );
        update_post_meta( $order_id, 'apollo_invoice_sent', false );

      } else if ($type === 'estimate') {
        update_post_meta( $order_id, 'apollo_estimate_id', $document_id );
        update_post_meta( $order_id, 'apollo_estimate_number', $document_number );
        update_post_meta( $order_id, 'apollo_estimate_sent', false );
      }

      // add to order notes
      $order->add_order_note( sprintf( __( 'Apollo %s created.', 'apollo-invoices' ), $type) );

      return $document_data;
    }

    // get invoice for order
    public static function getInvoice( $order_id ) {
      $id = get_post_meta( $order_id, 'apollo_invoice_id', true);
      if (!$id) {
        return false;
      }
      $number = get_post_meta( $order_id, 'apollo_invoice_number', true);
      $sent = get_post_meta( $order_id, 'apollo_invoice_sent', true);

      return array('id' => $id, 'number' => $number, 'sent' => $sent);
    }

    // get estimate for order
    public static function getEstimate( $order_id ) {
      $id = get_post_meta( $order_id, 'apollo_estimate_id', true);
      if (!$id) {
        return false;
      }
      $number = get_post_meta( $order_id, 'apollo_estimate_number', true);
      $sent = get_post_meta( $order_id, 'apollo_estimate_sent', true);

      return array('id' => $id, 'number' => $number, 'sent' => $sent);
    }

    // get PDF from Apoll, save it and return path or get path from local storage if it was already created
    public static function getPdf($id, $number, $type) {
      $lang = isset(get_option('apollo_general_settings')['apollo_document-lang'])
      ? get_option('apollo_general_settings')['apollo_document-lang']
      : 'wp-default';

      $lang = $lang == 'wp-default' ? explode('_', get_locale())[0] : $lang;

      if($lang == 'si') {
        if($type == 'invoice') {
          $type = 'račun';
        } elseif ($type == 'estimate') {
          $type = 'predračun';
        }
      }

      $pdf_path = APOLLO_DOCUMENTS_DIR."/".$id.".pdf";
      if (file_exists($pdf_path)) {
        return $pdf_path;
      }

      $token = get_option('apollo_general_settings')['apollo_token'];
      if(!$token) {
        return false;
      }

      Spaceinvoices\Spaceinvoices::setAccessToken($token);

      if($lang != 'default') {
        $pdf = Spaceinvoices\Documents::getPdf($id, $lang);
      } else {
        $pdf = Spaceinvoices\Documents::getPdf($id);
      }

      if(isset($pdf->error)) {
        return "Error creating PDF";
      }

      $pdf_path = APOLLO_DOCUMENTS_DIR."/".$id.".pdf";

      if(!file_exists(dirname($pdf_path)))
          mkdir(dirname($pdf_path), 0777, true);


      $fp = fopen($pdf_path,"wb");
      fwrite($fp,$pdf);
      fclose($fp);

      return $pdf_path;
    }

    // show PDF document, download from Apollo first if not in local storage yet
    public static function viewPdf($id, $number, $type) {
      $lang = isset(get_option('apollo_general_settings')['apollo_document-lang'])
      ? get_option('apollo_general_settings')['apollo_document-lang']
      : 'wp-default';

      $lang = $lang == 'wp-default' ? explode('_', get_locale())[0] : $lang;

      $type_name = $type;

      if($lang == 'si') {
        if($type == 'invoice') {
          $type_name = 'račun';
        } elseif ($type == 'estimate') {
          $type_name = 'predračun';
        }
      }

      $pdf_path = APOLLO_DOCUMENTS_DIR."/".$id.".pdf";
      if (!file_exists($pdf_path)) {
        $pdf_path = Apollo_invoice::getPdf($id, $number, $type);
      }
      header( 'Content-type: application/pdf' );
			header( 'Content-Disposition: inline; filename="' . basename( $pdf_path ) . '"' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Content-Length: ' . filesize( $pdf_path ) );
			header( 'Accept-Ranges: bytes' );

			readfile( $pdf_path );
      exit;
    }

    // get premises and devices from Apollo (fiscalization)
    public static function getBusinessPremises() {
      $output = get_option('apollo_general_settings');
      $token = $output['apollo_token'];
      $organization_id = $output['apollo_organization-id'];
      $response_array = array();
      $response_array['premises'] = array();
      $response_array['devices'] = array();

      Spaceinvoices\Spaceinvoices::setAccessToken($token);

      $premises = Spaceinvoices\Organizations::getBusinessPremises($organization_id);

      foreach ($premises as $premise) {
        $response_array['premises'][$premise->businessPremiseId] = $premise->businessPremiseId;
        $devices = array();

        foreach ($premise->electronicDevices as $device) {
          $devices[$device->electronicDeviceId] = $device->electronicDeviceId;
        }
        $response_array['devices'][$premise->businessPremiseId] = $devices;
      }

      return $response_array;
    }

    // get irganization data
    public static function getOrganization() {
      $output = get_option('apollo_general_settings');
      $token = $output['apollo_token'];
      $organization_id = $output['apollo_organization-id'];

      Spaceinvoices\Spaceinvoices::setAccessToken($token);

      return Spaceinvoices\Organizations::GetById($organization_id);
    }

    public static function getUnits() {
      $output = get_option('apollo_general_settings');
      $token = $output['apollo_token'];
      $organization_id = $output['apollo_organization-id'];

      Spaceinvoices\Spaceinvoices::setAccessToken($token);

      $units = ["" => "Brez"];

      $apolloUnits = Spaceinvoices\Organizations::getUnits($organization_id);

      foreach($apolloUnits as $apolloUnit) {
        $units[$apolloUnit->id] = $apolloUnit->name;
      }

      return $units;
    }
  }
}