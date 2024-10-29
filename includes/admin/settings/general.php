<?php
defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'Apollo_General_Settings' ) ) {

	class Apollo_General_Settings extends Apollo_Main_Settings {

		// here we define settings fields, from this we build settings page
		public function __construct() {
			$this->settings_key = 'apollo_general_settings';
			$this->settings_tab = __( 'General', 'apollo-invoices' );
			$this->fields       = $this->get_fields();
			$this->sections     = $this->get_sections();
			$this->defaults     = $this->get_defaults();

			parent::__construct();
		}

		private function get_sections() {
			// define sections, basically groups for settings
			$sections = array(
				'token'         => array(
					'title'       => __( 'Tokens Options', 'apollo-invoices' ),
					'description' => sprintf( __( 'You can get your token and organization ID at the <a target="_blank" href="%1$s">Apollo official page</a>, under Customizations, Integrations tab. The token and organization fields are required in order for the plugin to work.', 'apollo-invoices' ), 'https://getapollo.io'),
				),
				'fiscalization' => array(
					'title'       => __( 'Fiscalization Options', 'apollo-invoices' ),
					'description' => __( 'If you have setup up the fiscal verification of invoices on the Apollo page, you can set up your business premise and electronic device to verify invoices created here. You can setup Fiscalization on Apollo, in Organization settings, under the tab of Fiscalization','apollo-invoices' ),
				),
				'document'      => array(
          'title' => __( 'Documents Options', 'apollo-invoices' ),
					'description' =>
					sprintf(
						__( '<p><b>The PDF invoice will be attached to WooCommerce emails. The corresponding emails should be <a target="_blank" href="%1$s">enabled</a> in order to send the PDF invoice.</b></p>
						<p> Invoices will be automatically created for new orders, when paymend method is of the selected methods below. Estimates will be created, when new order payment method is "Bank transfer". Additonally when order with "Bank trasfer" method will be marked as "Completed", invoice will be created.</p>
						<p> You can manually create estimates or invoices for each order. You can send estimates or inovices (if both are created, only the invoice is sent) by choosing "Email invoice / order details to customer" option under "Order actions". Note that invoice/estimate PDF will be sent only if it was created before sending the email.</p>
					', 'apollo-invoices' ), 'admin.php?page=wc-settings&tab=email'),
				),
			);

			return $sections;
		}

		public function showError() {
			?>
				<div class="notice notice-error is-dismissible">
						<p><?= $this->errorMsg ?></p>
				</div>
			<?php
		}

		private function get_fields() {
			$this->errorMsg = __( 'Apollo error', 'apollo-invoices' );
			$valid_org = false;
			$fiscalization = false;
			$payments = array();
			$premises_data = array();
			$premises_data['premises'] = array();
			$premises_data['devices'] = array();
			$units = Apollo_invoice::getUnits();

			$output = get_option('apollo_general_settings');
     		$token = $output['apollo_token'];
			$organization_id = $output['apollo_organization-id'];

			// chec if token and organization are set, if not then hide all other settings, so we make sure those are valid first
			if($token && $organization_id) {
				$org = Apollo_invoice::getOrganization();

				// check if token and organization are correct
				if (!isset($org->error)) {
					$valid_org = true;
					$payments = WC()->payment_gateways->payment_gateways();
					$premises_data = Apollo_invoice::getBusinessPremises();

					// check if fiscalization is enabled on apollo side
					foreach ($org->_defaults as $def) {
						if ($def->name === 'furs_verifying') {
							$fiscalization = $def->value;
						}
					}

					$options = get_option('apollo_general_settings');
					$options['apollo_enable-fiscalization'] = $fiscalization ? '1' : '0';

					update_option('apollo_general_settings', $options);
				} else if (isset($org->error) && $org->error->statusCode === 401) {
					$this->errorMsg = __( 'Wrong token or organization id.', 'apollo-invoices' );
					add_action( 'admin_notices', array( $this, 'showError' ));
				} else if ( isset($org->error)) {
					$this->errorMsg = $org->error->message;
					add_action( 'admin_notices', array( $this, 'showError' ));
				} else {
					$this->errorMsg = __( 'Unknown error', 'apollo-invoices' );
					add_action( 'admin_notices', array( $this, 'showError' ));
				}
			}

			// these are inputis inside sections
			$settings = array(
				array(
							'id'       => 'apollo-token', // filed id
							'name'     => $this->prefix . 'token', // name attribute
							'title'    => __( 'Apollo token', 'apollo-invoices' ), // text that shows left from input
							'callback' => array( $this, 'input_callback' ), // callback to main-settings.php, input type depends on callback
							'page'     => $this->settings_key,
							'section'  => 'token', // one of the sections we defined on top
							'type'     => 'text', // input type
							'desc'     => '', // text that shows below the input
							'default'  => '', // default value
				),
				array(
					'id'       => 'apollo-organization-id',
					'name'     => $this->prefix . 'organization-id',
					'title'    => __( 'Organization ID', 'apollo-invoices' ),
					'callback' => array( $this, 'input_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'token',
					'type'     => 'text',
					'desc'     => '',
					'default'  => '',
				),
				array(
					'id'       => 'apollo-unit-id',
					'name'     => $this->prefix . 'unit-id',
					'title'    => __( 'Unit', 'apollo-invoices' ),
					'callback' => array( $this, 'select_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'token',
					'type'     => 'select',
					'desc'     => '<div class="apollo-notes">' . __( 'Edit your units on the Apollo webpage.', 'apollo-invoices' ) . '</div>',
					'options'  => !empty($units) ? $units : array(),
					'default'  => '',
					'class'	   => !$valid_org ? 'hidden' : '',
				),
				array(
					'id'       => 'apollo-enable-fiscalization',
					'name'     => $this->prefix . 'enable-fiscalization',
					'title'    => __( 'Fiscal verification', 'apollo-invoices' ),
					'callback' => array( $this, 'input_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'fiscalization',
					'type'     => 'checkbox',
					'desc'     => __( 'Enable fiscalization', 'apollo-invoices' )
					. '<br/><div class="apollo-notes">' .__('Make sure fiscalization details are set up on Apollo before enabling this option', 'apollo-invoices' ) . '</div>',
					'default'  => '',
					'class'		 => 'hidden',
					'value'		 => $fiscalization
				),
				array(
					'id'       => 'apollo-premise-id',
					'name'     => $this->prefix . 'premise-id',
					'title'    => __( 'Business Premise', 'apollo-invoices' ),
					'callback' => array( $this, 'select_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'fiscalization',
					'type'     => 'select',
					'desc'     => '<div class="apollo-notes">' . __( 'Edit your business premise on the Apollo webpage.', 'apollo-invoices' ) . '</div>',
					'options'  => $premises_data['premises'],
					'onchange' => 'updateDevices(this,'.json_encode($premises_data['devices']).')', // onChange JS action
					'default'  => '',
					'class'		 => !$valid_org || !$fiscalization ? 'hidden' : '',
				),
				array(
					'id'       => 'apollo-device-id',
					'name'     => $this->prefix . 'device-id',
					'title'    => __( 'Electronic Device', 'apollo-invoices' ),
					'callback' => array( $this, 'select_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'fiscalization',
					'type'     => 'select',
					'desc'     => '<div class="apollo-notes">' . __( 'Edit your electronic devices on the Apollo webpage.', 'apollo-invoices' ) . '</div>',
					'options'  => !empty($premises_data['devices']) ? reset($premises_data['devices']) : array(),
					'default'  => '',
					'class'		 => !$valid_org || !$fiscalization ? 'hidden' : '',
				),
				array(
					'id'       => 'apollo-operator-tax-number',
					'name'     => $this->prefix . 'operator-tax-number',
					'title'    => __( 'Operator Tax Number', 'apollo-invoices' ),
					'callback' => array( $this, 'input_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'fiscalization',
					'type'     => 'text',
					'desc'     => '<div class="apollo-notes">' . __( "Insert the personal tax number of the company-authorised representative (optional if this person does not have a Slovenian Tax Number).", 'apollo-invoices' ) . '</div>',
					'default'  => '',
					'class'		 => !$valid_org || !$fiscalization ? 'hidden' : '',
				),
				array(
					'id'       => 'apollo-document-lang',
					'name'     => $this->prefix . 'document-lang',
					'title'    => __( 'Document lanuguage', 'apollo-invoices' ),
					'callback' => array( $this, 'select_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'document',
					'type'     => 'select',
					'desc'     => '<div class="apollo-notes">' . __( "Default lanuguage when creating Apollo documents.", 'apollo-invoices' ) . '</div>',
					'default'  => '',
					'options' =>  array(
						'wp-default' => 'Wordpress default',
						'default' => 'Apollo default',
						'en' => 'English',
						'de' => 'German',
						'hr' => 'Croatian',
						'it' => 'Italian',
						'fr' => 'French',
						'cz' => 'Czech',
						'sk' => 'Slovak',
						'sl' => 'Slovenian',
					),
					'class'		 => !$valid_org ? 'hidden' : '',
				),
				array(
					'id'       => 'apollo-category-alias',
					'name'     => $this->prefix . 'category-alias',
					'title'    => __( 'Income categorization alias', 'apollo-invoices' ),
					'callback' => array( $this, 'input_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'document',
					'type'     => 'text',
					'desc'     => '<div class="apollo-notes">' . __( "Set income category for documents. You can edit categories on Apollo webpage under 'Business' > 'Categories'. Allowed chacters are a-z, numbers and '-'.", 'apollo-invoices' ) . '</div>',
					'default'  => '',
					'placeholder' => 'woocommerce-shop',
					'onkeypress' => "return aliasInput(event)",
					'class'		 => !$valid_org ? 'hidden' : '',
				),
				array(
					'id'       => 'apollo-add-sku',
					'name'     => $this->prefix . 'add-sku',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'document',
					'type'     => 'checkbox',
					'desc'     => __( 'Add SKU code to invoice', 'apollo-invoices' )
					              . '<br/><div class="apollo-notes">' . __( 'Add SKU code on invoice for each item. SKU code shows in item description for item that have SKU code set.', 'apollo-invoices' ) . '</div>',
					'default'  => 0,
					'class'		 => !$valid_org ? 'hidden' : '',
				),
				array(
					'id'       => 'apollo-send-estimate',
					'name'     => $this->prefix . 'send-estimate',
					'title'    => __( 'Direct bank transfer', 'apollo-invoices' ),
					'callback' => array( $this, 'input_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'document',
					'type'     => 'checkbox',
					'desc'     => __( 'Send estimate automatically', 'apollo-invoices' )
					              . '<br/><div class="apollo-notes">' . __( 'Only sends the estimate if the order payment type is "Direct bank transfer".', 'apollo-invoices' ) . '</div>',
					'default'  => 0,
					'class'		 => !$valid_org ? 'hidden' : '',
				),
				array(
					'id'       => 'apollo-send-bank-invoice',
					'name'     => $this->prefix . 'send-bank-invoice',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'document',
					'type'     => 'checkbox',
					'desc'     => __( 'Send invoice automatically', 'apollo-invoices' )
					              . '<br/><div class="apollo-notes">' . __( 'Attach PDF invoice to customer mail, when order status is "Completed" and payment method is "Direct bank transfer".', 'apollo-invoices' ) . '</div>',
					'default'  => 0,
					'class'		 => !$valid_org ? 'hidden' : '',
				),
				array(
					'id'       => 'apollo-cod-invoice',
					'name'     => $this->prefix . 'cod-invoice',
					'title'    => __( 'Cash on delivery', 'apollo-invoices' ),
					'callback' => array( $this, 'input_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'document',
					'type'     => 'checkbox',
					'desc'     => __( 'Create invoice when order is "Completed"', 'apollo-invoices' )
					              . '<br/><div class="apollo-notes">' . __( 'By default invoice is created for NEW orders with payment method "Cash on delivery".<br/> By checking this option, invoice will be created when order is "Completed" insetad.', 'apollo-invoices' ) . '</div>',
					'default'  => 0,
					'class'		 => !$valid_org ? 'hidden' : '',
				),
				array(
					'id'       => 'apollo-send-invoice',
					'name'     => $this->prefix . 'send-invoice',
					'title'    => __( 'Other payment methods', 'apollo-invoices' ),
					'callback' => array( $this, 'input_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'document',
					'type'     => 'checkbox',
					'desc'     => __( 'Send invoice automatically', 'apollo-invoices' )
												. '<br/><div class="apollo-notes">' . __( 'The invoice will be attached to order, if the order payment matches one of the chosen payment gateways. You can only choose from gateways that are enabled ( you can enable them in WooCommerce settings, payments tab).', 'apollo-invoices' ) . '</div><br/>',
					'default'  => 0,
					'class'		 => !$valid_org ? 'hidden' : '',
				),
			);

			$i = 0;

			// build list of possible payment methods from Woocommerce
			foreach ($payments as $key => $payment) {
				if ($payment->id !== 'bacs' && $payment->id !== 'cod') {

					$settings[] = array(
						'id'       => 'apollo-payment-'.$payment->id,
						'name'     => $this->prefix . 'payment-'.$payment->id,
						'title'    => '',
						'callback' => array( $this, 'input_callback' ),
						'page'     => $this->settings_key,
						'section'  => 'document',
						'type'     => 'checkbox',
						'desc'     => $payment->title,
						'default'  => 0,
						'disabled' => $payment->enabled !== 'yes', // disable if payment not enabled in Woocommerce settings
						'class'		 => !$valid_org ? 'hidden payment-cb' : 'payment-cb', // hide settings if organzation or token invalid
					);
				}
			}

			return apply_filters( 'apollo_general_settings', $settings );
    }

		// input sanitizing. Not really needed, good to have I guess...
    public function sanitize( $input ) {
			$output = get_option( $this->settings_key );

			foreach ( $output as $key => $value ) {
				if ( ! isset( $input[ $key ] ) ) {
					$output[ $key ] = is_array( $output[ $key ] ) ? array() : '';
					continue;
				}

				if ( is_array( $output[ $key ] ) ) {
					$output[ $key ] = $input[ $key ];
					continue;
				}

				$output[ $key ] = stripslashes( $input[ $key ] );
			}

			return apply_filters( 'apollo_sanitized_' . $this->settings_key, $output, $input );
		}
	}
}
