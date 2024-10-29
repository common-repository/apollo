<?php
/**
 * Plugin Name:       Apollo
 * Plugin URI:        https://wordpress.org/plugins/apollo
 * Description:       Manually or automatically generate invoices and send PDFs as attachments for WooCommerce orders.
 * Version:           1.1.19
 * Author:            Studio404
 * Text Domain:       apollo
 * Domain Path:       /languages
 * WC tested up to:   5.7.0
 * WC requires at least: 3.0
 */

defined( 'ABSPATH' ) or exit;

define( 'APOLLO_VERSION', '1.1.19' );

function apollo_load_plugin() {

  // define variables and include all files that are needed
  if ( ! class_exists( 'WooCommerce' ) ) {
    return;
  }

  if ( ! defined( 'APOLLO_FILE' ) ) {
    define( 'APOLLO_FILE', __FILE__ );
  }

  if ( ! defined( 'APOLLO_DIR' ) ) {
    define( 'APOLLO_DIR', plugin_dir_path(__FILE__ ));
  }

  if ( ! defined( 'APOLLO_DOCUMENTS_DIR' ) ) {
    define( 'APOLLO_DOCUMENTS_DIR', wp_upload_dir()['basedir'] . '/apollo-documents' );
  }

  if ( ! defined( 'APOLLO_URL' ) ) {
    define( 'APOLLO_URL', untrailingslashit( plugins_url( '', APOLLO_FILE ) ) );
  }

  if ( file_exists( APOLLO_DIR . 'includes/apollo.php' ) ) {
    require_once APOLLO_DIR . 'includes/apollo.php';
  }

  if ( file_exists( APOLLO_DIR . 'includes/admin/settings/main-settings.php' ) ) {
    require_once APOLLO_DIR . 'includes/admin/settings/main-settings.php';
  }

  if ( file_exists( APOLLO_DIR . 'includes/admin/settings/general.php' ) ) {
    require_once APOLLO_DIR . 'includes/admin/settings/general.php';
  }

  if ( file_exists( APOLLO_DIR . 'includes/admin/invoice.php' ) ) {
    require_once APOLLO_DIR . 'includes/admin/invoice.php';
  }

  if ( file_exists( APOLLO_DIR . 'vendor/spaceinvoices/vendor/autoload.php' ) ) {
    require_once APOLLO_DIR . 'vendor/spaceinvoices/vendor/autoload.php';
  }

  Apollo::instance();
}

// calls funtion above with WP plugins_loaded hook
add_action( 'plugins_loaded', 'apollo_load_plugin' );

add_filter( 'woocommerce_email_attachments', 'add_apollo_document_to_email', 10, 3 );
function add_apollo_document_to_email( $attachments, $status, $order ) {
  // this is called when Woocommerce email is sent, functions creates/sends apollo documents based on settings

  // Only add attachment if new order, completed order or invoice mail
  if($status != 'customer_invoice' && $status != 'new_order'  && $status != 'customer_completed_order' && $status != 'customer_on_hold_order' && $status != 'customer_processing_order') {
    return;
  }

  // only send attachments for new order if payment 
  if($status == 'new_order' && !($order->get_payment_method() == 'bacs' || $order->get_payment_method() == 'cod')) {
    return;
  };

  $lang = explode('_', get_locale())[0];

  $auto_invoice = (bool) get_option('apollo_general_settings')['apollo_send-invoice']; // send invoice automatically setting
  $auto_invoice_bank = (bool) get_option('apollo_general_settings')['apollo_send-bank-invoice']; // send invoice automatically when payment is bank transfer
  // $auto_invoice_status = get_option('apollo_general_settings')['apollo_invoice-status'];
  $invoice_id = get_post_meta( $order->get_id(), 'apollo_invoice_id', true); // gets apollo invoice id, if it exist
  $invoice_number = get_post_meta( $order->get_id(), 'apollo_invoice_number', true);
  $pdf_invoice_path = APOLLO_DOCUMENTS_DIR."/invoice - ".$invoice_number.".pdf";

  $auto_estimate = (bool) get_option('apollo_general_settings')['apollo_send-estimate']; // send estimate automatically setting
  $estimate_number = get_post_meta( $order->get_id(), 'apollo_estimate_number', true);
  $estimate_id = get_post_meta( $order->get_id(), 'apollo_estimate_id', true);
  $pdf_estimate_path = APOLLO_DOCUMENTS_DIR."/estimate - ".$estimate_number.".pdf";
  $payment_type = 'apollo_payment-'.$order->get_payment_method();
  $order_paid = get_post_meta( $order->get_id(), '_date_paid', true);

  $auto_create_cod = (bool) get_option('apollo_general_settings')['apollo_cod-invoice'];

  //chechk if order payment matches any of the payments set in apollo settings
  $payment_enabled = isset(get_option('apollo_general_settings')[$payment_type])  ? (bool) get_option('apollo_general_settings')[$payment_type] : false;

  if ($lang === 'sl') {
    $pdf_invoice_path = APOLLO_DOCUMENTS_DIR."/račun - ".$invoice_number.".pdf";
    $pdf_estimate_path = APOLLO_DOCUMENTS_DIR."/predračun - ".$estimate_number.".pdf";
  }

  if($invoice_id && $status === 'customer_invoice') { // sent maunally from order (invoice)
    if(file_exists($pdf_invoice_path)) {
      $attachments[] = $pdf_invoice_path;
    } else {
      $attachments[] = Apollo_invoice::getPdf($invoice_id, $invoice_number, 'invoice');
    }
    update_post_meta( $order->get_id(), 'apollo_invoice_sent', true );

  } else if($estimate_id && $status === 'customer_invoice') { // sent maunally from order (estimate)
    if(file_exists($pdf_estimate_path)) {
      $attachments[] = $pdf_estimate_path;
    } else {
      $attachments[] = Apollo_invoice::getPdf($estimate_id, $estimate_number, 'estimate');
    }
    update_post_meta( $order->get_id(), 'apollo_estimate_sent', true );

  } else if ($auto_invoice_bank && $order->get_payment_method() === 'bacs' && $status === 'customer_completed_order') { // bank transfer order completed - send invoice (if auto sending enabled in settigns)
    $invoice = Apollo_invoice::create($order->get_id(), 'invoice');
    $attachments[] = Apollo_invoice::getPdf($invoice['id'], $invoice['number'], 'invoice');
    update_post_meta( $order->get_id(), 'apollo_invoice_sent', true );

  } else if ($auto_estimate && $order->get_payment_method() === 'bacs' && $status !== 'customer_completed_order') { // new order; bank transfer
    $estimate = Apollo_invoice::create($order->get_id(), 'estimate');
    $attachments[] = Apollo_invoice::getPdf($estimate['id'], $estimate['number'], 'estimate');
    update_post_meta( $order->get_id(), 'apollo_estimate_sent', true );

  } else if ($auto_invoice && $payment_enabled && $order_paid !== '') { // new order; status matches invoice settings
    $invoice = Apollo_invoice::create($order->get_id(), 'invoice');
    $attachments[] = Apollo_invoice::getPdf($invoice['id'], $invoice['number'], 'invoice');
    update_post_meta( $order->get_id(), 'apollo_invoice_sent', true );

  } else if ($auto_invoice && $order->get_payment_method() == 'cod' && !$auto_create_cod && ($status == 'new_order' ||  $status != 'customer_on_hold_order' || $status != 'customer_processing_order' ) ) { // new order; status matches invoice settings and payment is cash on delivery
    $invoice = Apollo_invoice::create($order->get_id(), 'invoice');
    $attachments[] = Apollo_invoice::getPdf($invoice['id'], $invoice['number'], 'invoice');
    update_post_meta( $order->get_id(), 'apollo_invoice_sent', true );

  } else if ($auto_invoice && $order->get_payment_method() == 'cod' && $auto_create_cod && $status == 'customer_completed_order' ) { // order completed; status matches invoice settings and payment is cash on delivery
    $invoice = Apollo_invoice::create($order->get_id(), 'invoice');
    $attachments[] = Apollo_invoice::getPdf($invoice['id'], $invoice['number'], 'invoice');
    update_post_meta( $order->get_id(), 'apollo_invoice_sent', true );

  // } else if (!$invoice_id && ($order->get_payment_method() === 'paypal' || $order->get_payment_method() === 'stripe') && !$auto_invoice) {
  //   // if not auto sending still create the invoice in case paypal or stripe
  //   $invoice = Apollo_invoice::create($order->get_id(), 'invoice');
  }

  return $attachments;
}

add_action( 'woocommerce_subscription_renewal_payment_complete', 'action_woocommerce_renewal', 10, 3 );
function action_woocommerce_renewal( $last_order ) {
  action_woocommerce_new_order($last_order->get_id());
}

add_action( 'woocommerce_checkout_order_processed', 'action_woocommerce_new_order', 10, 3 );
function action_woocommerce_new_order( $order_id ) {
  // this function is called when new order is created in Woocommerce
  
  $order = wc_get_order($order_id);

  $payment_type = 'apollo_payment-'.$order->get_payment_method();
  $auto_create_cod = (bool) get_option('apollo_general_settings')['apollo_cod-invoice'];

  //chechk if order payment matches any of the payments set in apollo settings
  $payment_enabled = isset(get_option('apollo_general_settings')[$payment_type]) ? (bool) get_option('apollo_general_settings')[$payment_type] : false;

  if ( $order->get_payment_method() === 'bacs') { // new order; bank transfer
    Apollo_invoice::create($order_id, 'estimate');
  } else if ($order->get_payment_method() === 'cod' && !$auto_create_cod) {
    Apollo_invoice::create($order_id, 'invoice');
  }
};

add_action( 'woocommerce_order_status_completed',	'action_woocommerce_status_completed');
function action_woocommerce_status_completed ($order_id) {
  // this function is called when order is marked as completed

  $order = wc_get_order($order_id);
  $auto_create_cod = (bool) get_option('apollo_general_settings')['apollo_cod-invoice'];

  // $payment_method = $order->get_payment_method();

  $payment_type = 'apollo_payment-'.$order->get_payment_method();
  $payment_enabled = isset(get_option('apollo_general_settings')[$payment_type]) ? (bool) get_option('apollo_general_settings')[$payment_type] : false;

  $order_paid = get_post_meta( $order->get_id(), '_date_paid', true);

  // $email = new WC_Email_Customer_Completed_Order();

  // If paid online and order completed email enabled then skip creating invoice
  // as it will be created in attachment hook to prevent double issuing in case both
  // hooks get processed at the same time
  // if (($payment_method === 'paypal' || $payment_method === 'stripe') && $email->is_enabled()) {
  //   return;
  // }

  // create invoice for bank transfer orders, when order status is set to completed
  if(($order->get_payment_method() === 'bacs') || ($payment_enabled && $order_paid !== '')) {
    Apollo_invoice::create($order_id, 'invoice');
  } else if ($order->get_payment_method() === 'cod' && $auto_create_cod) {
    Apollo_invoice::create($order_id, 'invoice');
  }
}