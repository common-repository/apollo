<?php
defined( 'ABSPATH' ) or exit;

/*	This is class that builds settings based on general.php file.
		If you need to add new input type or change the behaviour of settings, it should be done here.
		If you need to remove/add/ update settings fields, look in general.php file.	
*/
abstract class Apollo_Main_Settings {

	protected $prefix = 'apollo_';

	public $settings_key;

	public $settings_tab;

	protected $sections = array();

	protected $fields = array();

	protected $defaults = array();

	private static $settings = array();

	public function __construct() {
		$this->add_sections();
		$this->add_fields();
		$this->set_defaults();

		register_setting( $this->settings_key, $this->settings_key, array( $this, 'sanitize' ) );

		if (self::settings_capability() != 'manage_options') {
			add_filter( 'option_page_capability_'. $this->settings_key, array( __CLASS__, 'settings_capability' ) );
		}

	}

	public static function init_hooks() {

		add_action( 'admin_menu', array( __CLASS__, 'add_wc_submenu_options_page' ) );
		add_action( 'admin_notices', array( __CLASS__, 'display_settings_errors' ) );

		$page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
			$action = isset($_POST['action']) ? sanitize_key($_POST['action']) : '';

			if ($page === 'apollo-invoices' || $action === 'update') {
				add_action( 'admin_init', array( __CLASS__, 'load_settings' ) );
			}
	}

	public static function load_settings() {
		$settings[] = new Apollo_General_Settings();
		self::$settings = apply_filters( 'apollo_settings', $settings );
	}

	public static function add_wc_submenu_options_page() {
		add_submenu_page( 'woocommerce', __( 'Apollo', 'apollo-invoices' ), __( 'Apollo', 'apollo-invoices' ), self::settings_capability(), 'apollo-invoices', array( __CLASS__, 'display_options_page' ) );
	}

	public static function settings_capability() {
		return apply_filters('apollo_settings_capability', 'manage_options');
	}

	// main function for settings
	public static function display_options_page() {
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'apollo_general_settings';
		?>

		<div class="wrap apollo">
			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( self::$settings as $setting ) {
					$active = $current_tab === $setting->settings_key ? 'nav-tab-active' : '';
					printf( '<a class="nav-tab %1$s" href="?page=apollo-invoices&tab=%2$s">%3$s</a>', $active, $setting->settings_key, $setting->settings_tab );
				}

				$tabs = apply_filters( 'apollo_settings_tabs', array() );
				foreach ( $tabs as $settings_key => $settings_tab ) {
					$active = $current_tab === $settings_key ? 'nav-tab-active' : '';
					printf( '<a class="nav-tab %1$s" href="?page=apollo-invoices&tab=%2$s">%3$s</a>', $active, $settings_key, $settings_tab );
				}
				?>
			</h2>
			<form method="post" action="options.php" enctype="multipart/form-data">
				<?php
				settings_fields( $current_tab );
				do_settings_sections( $current_tab );

				submit_button();
				?>
			</form>
		</div>

		<?php
	}

	public static function display_settings_errors() {
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'apollo_general_settings';
		settings_errors( $current_tab );
	}

	private function add_sections() {
		foreach ( $this->sections as $id => $section ) {
			add_settings_section( $id, $section['title'], function() use ( $section ) {
				if ( isset( $section['description'] ) ) {
					echo $section['description'];
				}
			}, $this->settings_key );
		}
	}

	public static function display_settings_notices( $settings_key ) {
		settings_errors( $settings_key );
	}

	protected function add_fields() {
		foreach ( $this->fields as $field ) {
			add_settings_field( $field['name'], $field['title'], $field['callback'], $field['page'], $field['section'], $field );
		};
	}

	// function for building select input
	public function select_callback( $args ) {
		$options = get_option( $args['page'] );
		$onchage = isset($args['onchange']) ? $args['onchange'] : '';
		?>
		<select id="<?php echo $args['id']; ?>" name="<?php echo $args['page'] . '[' . $args['name'] . ']'; ?>" onchange='<?php echo $onchage ?>''>
			<?php
			foreach ( $args['options'] as $key => $label ) :
				?>
				<option
					value="<?php echo esc_attr( $key ); ?>" <?php selected( $options[ $args['name'] ], $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php
			endforeach;
			?>
		</select>
		<div class="apollo-notes"><?php echo $args['desc']; ?></div>
		<?php
	}

	// function for building text input AND checbox (could made them seperate function tho...)
	public function input_callback( $args ) {
		$options = get_option( $args['page'] );
		$class = isset( $args['class'] ) ? $args['class'] : '';
		$is_checkbox = $args['type'] === 'checkbox';
		$is_disabled = isset($args['disabled']) ? $args['disabled'] : false;

		if ( $is_checkbox ) { ?>
			<input type="hidden" name="<?php echo $args['page'] . '[' . $args['name'] . ']'; ?>" value="0"/>
		<?php } ?>
		<input id="<?php echo $args['id']; ?>"
		       name="<?php echo $args['page'] . '[' . $args['name'] . ']'; ?>"
		       type="<?php echo $args['type']; ?>"
		       value="<?php echo $is_checkbox ? 1 : esc_attr( $options[ $args['name'] ] ); ?>"
					 placeholder="<?php echo isset($args['placeholder']) ? $args['placeholder'] : ''; ?>"
					 onkeypress="<?php echo isset($args['onkeypress']) ? $args['onkeypress'] : ''; ?>"
		       <?php echo $is_disabled ? 'disabled' : ''; ?>

			<?php
			if ( $is_checkbox ) {
				checked( $options[ $args['name'] ] );
			}

			if ( isset( $args['attrs'] ) ) {
				foreach ( $args['attrs'] as $attr ) {
					echo $attr . ' ';
				}
			}
			?>
		/>
		<?php if ( $is_checkbox ) { ?>
			<label for="<?php echo $args['id']; ?>" class="<?php echo $class; ?>"><?php echo $args['desc']; ?></label>
		<?php } else { ?>
			<div class="<?php echo $class; ?>"><?php echo $args['desc']; ?></div>
		<?php } ?>
		<?php
	}

	protected function get_defaults() {
		$fields   = $this->fields;
		$defaults = array();

		$defaults = wp_parse_args( $defaults, wp_list_pluck( $fields, 'default', 'name' ) );

		return $defaults;
	}

	protected function set_defaults() {
		$options = get_option( $this->settings_key );

		if ( $options === false ) {
			return add_option( $this->settings_key, $this->defaults );
		}

		foreach ( $this->defaults as $key => $value ) {

			if ( ! isset( $options[ $key ] ) ) {
				continue;
			}

			$this->defaults[ $key ] = $options[ $key ];
		}

		return update_option( $this->settings_key, $this->defaults );
	}

	public abstract function sanitize( $input );
}
