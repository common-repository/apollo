<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'Apollo' ) ) {
  final class Apollo {
    protected static $_instance = null;
		private $prefix = 'apollo_';
    public $settings = array();

    public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

    private function __construct() {
			$this->errorMsg = __( 'Apollo error', 'apollo-invoices' );
			$this->successMsg = __( 'Apollo success', 'apollo-invoices' );

			// check if user admin
			if ( !current_user_can('administrator') && !current_user_can('editor') && !current_user_can('shop_manager')) {
				return;
			}

			$wp_upload_dir = wp_upload_dir();

			add_filter( 'plugin_action_links_' . plugin_basename( APOLLO_FILE ), array($this,'add_action_links') );

			Apollo_Main_Settings::init_hooks();

			add_action( 'add_meta_boxes', array( $this, 'add_apollo_boxes' ) );
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_row_meta' ), 10, 2 );
			add_action( 'admin_init', array( $this, 'admin_pdf_callback' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'my_admin_scripts' ) );
		}

		// add css and js
		public function my_admin_scripts() {
			//css
			wp_register_style( 'apollo_settings_css', APOLLO_URL . '/admin/css/custom.css');
			wp_enqueue_style('apollo_settings_css');

			//js
			wp_register_script( 'apollo_settings_js', APOLLO_URL . '/admin/js/custom.js' );
			wp_enqueue_script( 'apollo_settings_js' );
		}


		// get and show PDF for document
		public function admin_pdf_callback() {
			$action = isset($_GET['apollo_action']) ? sanitize_key($_GET['apollo_action']) : '';
			$order_id = isset($_GET['post']) ? intval( $_GET['post'] ): 0;
			$type = isset($_GET['apollo_type']) ? sanitize_key($_GET['apollo_type']) : 'invoice';
			$apollo_document_id = isset($_GET['apollo_document_id']) ? sanitize_key($_GET['apollo_document_id']) : false;
			$apollo_document_number = isset($_GET['apollo_document_number']) ? sanitize_key($_GET['apollo_document_number']) : false;

			if ($action === 'create') {

				// using nonces for extra security (so call can't be duplicated)
				$nonce = sanitize_key( $_GET['nonce'] );
				if ( ! wp_verify_nonce( $nonce, $action ) ) {
					wp_die( 'Invalid request.' );
				}

				$callback = Apollo_invoice::create($order_id, $type);

				if (isset($callback['error'])) {
					$this->errorMsg = $callback['error'];
					add_action( 'admin_notices', array( $this, 'apollo_error_notice' ));
				} else if ($callback && !isset($callback['exsists'])) {
					$this->successMsg = __( 'Successfully created Apollo', 'apollo-invoices' ). " $type";
					add_action( 'admin_notices', array( $this, 'apollo_success_notice' ));
				} else {
					$this->errorMsg = __( 'There was an error.', 'apollo-invoices' );
					add_action( 'admin_notices', array( $this, 'apollo_error_notice' ));
				}

			} else if ($action === 'pdf' && $apollo_document_id) {

				$nonce = sanitize_key( $_GET['nonce'] );
				if ( ! wp_verify_nonce( $nonce, $action ) ) {
					wp_die( 'Invalid request.' );
				}
				Apollo_invoice::viewPdf($apollo_document_id, $apollo_document_number, $type);
			}
		}

		function apollo_error_notice() {
			?>
			<div class="notice notice-error is-dismissible">
					<p><?= $this->errorMsg ?></p>
			</div>
			<?php
		}

		function apollo_success_notice() {
			?>
			<div class="notice notice-success is-dismissible">
					<p><?= $this->successMsg ?></p>
			</div>
			<?php
		}

		// add Apollo estimate and invoice boxes on right side in order view
		function add_apollo_boxes() {
			add_meta_box( 'apollo_estimate', __( 'Apollo - estimate', 'apollo-invoices' ), array( $this, 'display_apollo_estimate_box' ), 'shop_order', 'side', 'high' );
			add_meta_box( 'apollo_invoice', __( 'Apollo - invoice', 'apollo-invoices' ), array( $this, 'display_apollo_invoice_box' ), 'shop_order', 'side', 'high' );
    }

    function add_action_links( $links ) {
			$settings_url = add_query_arg( array( 'page' => 'apollo-invoices' ), admin_url( 'admin.php' ) );
			array_unshift( $links, sprintf( '<a href="%1$s">%2$s</a>', $settings_url, __( 'Settings', 'apollo-invoices' ) ) );

			return $links;
    }

		// show invoice box with invoice info, or CREATE button if invoice for order was not yet created
    public function display_apollo_invoice_box( $order ) {
			$invoice = Apollo_invoice::getInvoice( $order->ID );

			// show CREATE button if invoice not created
			if ( !$invoice ) {
				$url = wp_nonce_url(add_query_arg( array(
					'post' => $order->ID,
					'action' => 'edit',
					'apollo_action' => 'create',
					'apollo_type' => 'invoice'
			), admin_url( 'post.php' )), 'create', 'nonce' );

			$wc_order = wc_get_order($order->ID);

			// if no payment method, show warning when creating invoice
			if ($wc_order->get_payment_method() === '') {
				printf( '<a class="button order-page invoice apollo" onclick="notPaidWarn(`%1$s`)" title="%2$s">%3$s</a>', $url, __( 'Create invoice (also marks is as paid, so generate it after order was paid)', 'apollo-invoices' ), __( 'Create', 'apollo-invoices') );
			} else {
				printf( '<a class="button order-page invoice apollo" href="%1$s" title="%2$s">%3$s</a>', $url, __( 'Create invoice (also marks is as paid, so generate it after order was paid)', 'apollo-invoices' ), __( 'Create', 'apollo-invoices'));
			}

			// if invoice was created, show invoice data
			} else {
				echo "<table class='order-page-meta-box pdf-invoice apollo'>";

				printf( '<tr>' );
				printf( '<td>%s</td>', __( 'Number:', 'apollo-invoices' ) );
				printf( '<td>%s</td>', $invoice['number'] );
				printf( '</tr>' );

				printf( '<tr>' );
				printf( '<td class="pointer" title="%1$s">%2$s</td>', __( 'You can read about sending inovices in Apollo settings, under Mailing Options', 'apollo-invoices' ), __( 'Sent:', 'apollo-invoices' ) );
				printf( '<td>%s</td>', (bool) $invoice['sent'] ? __( 'Yes', 'apollo-invoices' ) : __( 'No', 'apollo-invoices' ) );
				printf( '</tr>' );

				echo "</table>";

				echo '<p class="invoice-actions">';

				$org_id = get_option('apollo_general_settings')['apollo_organization-id'];

				$view_url = "https://getapollo.io/app/$org_id/documents/o/view/".$invoice['id'];

				printf( '<a class="button order-page invoice apollo" target="_blank" href="%1$s" title="%2$s">%3$s</a>', $view_url, __( 'View invoice on Apollo page', 'apollo-invoices' ), __( 'View invoice', 'apollo-invoices'));

				$download_pdf_url = wp_nonce_url(add_query_arg( array(
					'post' => $order->ID,
					'action' => 'edit',
					'apollo_action' => 'pdf',
					'apollo_document_id' => $invoice['id'],
					'apollo_document_number' => $invoice['number'],
					'apollo_type' => 'invoice'
				), admin_url( 'post.php' )), 'pdf', 'nonce');

				printf( '<a class="button order-page invoice apollo" target="_blank" href="%1$s" title="%2$s">%3$s</a>', $download_pdf_url, __( 'View invoice PDF', 'apollo-invoices' ), __( 'View PDF', 'apollo-invoices'));

				echo '</p>';

			}
		}

		// show estimate box, same as invoice but for estimates
		public function display_apollo_estimate_box( $order ) {
			$estimate = Apollo_invoice::getEstimate( $order->ID );

			if ( !$estimate ) {
				$url = wp_nonce_url(add_query_arg( array(
					'post' => $order->ID,
					'action' => 'edit',
					'apollo_action' => 'create',
					'apollo_type' => 'estimate'
			), admin_url( 'post.php' )), 'create', 'nonce' );

				printf( '<a class="button order-page invoice apollo" href="%1$s" title="%2$s">%3$s</a>', $url,  __( 'Create estimate', 'apollo-invoices' ), __( 'Create', 'apollo-invoices'));

			} else {

				echo "<table class='order-page-meta-box pdf-invoice apollo'>";

				printf( '<tr>' );
				printf( '<td>%s</td>', __( 'Number:', 'apollo-invoices' ) );
				printf( '<td>%s</td>', $estimate['number'] );
				printf( '</tr>' );

				printf( '<tr>' );
				printf( '<td class="pointer" title="%1$s">%2$s</td>', __( 'You can read about sending estimates in Apollo settings, under Mailing Options', 'apollo-invoices' ), __( 'Sent:', 'apollo-invoices' ) );
				printf( '<td>%s</td>', (bool) $estimate['sent'] ? __( 'Yes', 'apollo-invoices' ) : __( 'No', 'apollo-invoices' ) );
				printf( '</tr>' );

				echo "</table>";

				echo '<p class="invoice-actions">';

				$org_id = get_option('apollo_general_settings')['apollo_organization-id'];

				$view_url = "https://getapollo.io/app/$org_id/documents/o/view/".$estimate['id'];

				printf( '<a class="button order-page invoice apollo" target="_blank" href="%1$s" title="%2$s">%3$s</a>', $view_url, __( 'View estimate on Apollo page', 'apollo-invoices' ), __( 'View estimate', 'apollo-invoices'));

				$download_pdf_url = wp_nonce_url(add_query_arg( array(
					'post' => $order->ID,
					'action' => 'edit',
					'apollo_action' => 'pdf',
					'apollo_document_id' => $estimate['id'],
					'apollo_document_number' => $estimate['number'],
					'apollo_type' => 'estimate'
				), admin_url( 'post.php' )), 'pdf', 'nonce' );

				printf( '<a class="button order-page invoice apollo" target="_blank" href="%1$s" title="%2$s">%3$s</a>', $download_pdf_url, __( 'View estimate PDF', 'apollo-invoices' ), __( 'View PDF', 'apollo-invoices'));

				echo '</p>';

			}
		}

		public static function add_plugin_row_meta( $links, $file ) {
			if ( plugin_basename( APOLLO_FILE ) === $file ) {
				$url   = 'https://getapollo.io';
				$title = __( 'Visit Apollo', 'apollo-invoices' );
				$links[] = sprintf( '<a href="%1$s" target="_blank">%2$s</a>', $url, $title );
			}

			return $links;
		}
  }
}