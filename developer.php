<?php /*

**************************************************************************

Plugin Name:  Ultimate Comment Pack
Plugin URI:   http://wordpress.org/extend/plugins/ultimate-comment-pack/
Description:  A curated collection of best practices commenting plugins.
Version:      1.2.6
Author:       Postmatic
Author URI:   http://gopostmatic.com
License:      GPLv2 or later

Text Domain:  ucp-developer
Domain Path:  /languages/

**************************************************************************/

// Load helper class if installing a plugin
if ( ! empty( $_POST['action'] ) && 'ucp_developer_install_plugin' == $_POST['action'] )
	require_once( dirname( __FILE__ ) . '/includes/class-empty-upgrader-skin.php' );


class Automattic_Developer {

	public $settings               = array();
	public $default_settings       = array();

	const VERSION                  = '1.2.6';
	const OPTION                   = 'ucp_developer';
	const PAGE_SLUG                = 'ucp_developer';

	private $recommended_plugins   = array();
	private $recommended_constants = array();

	function __construct() {
		add_action( 'init', 									array( $this, 'load_plugin_textdomain') );
		add_action( 'init',										array( $this, 'init' ) );
		add_action( 'admin_init',								array( $this, 'admin_init' ) );

		add_action( 'admin_menu',								array( $this, 'register_settings_page' ) );
		add_action( 'admin_bar_menu',							array( $this, 'add_node_to_admin_bar' ) );

		add_action( 'admin_enqueue_scripts',					array( $this, 'load_settings_page_script_and_style' ) );

		add_action( 'wp_ajax_ucp_developer_lightbox_step_1',	array( $this, 'ajax_handler' ) );
		add_action( 'wp_ajax_ucp_developer_install_plugin',		array( $this, 'ajax_handler' ) );
		add_action( 'wp_ajax_ucp_developer_activate_plugin',	array( $this, 'ajax_handler' ) );

	}

	// Internationalization
	function load_plugin_textdomain () {
		load_plugin_textdomain ( 'ucp-developer', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	// Allows private variables to be read. Basically implements read-only variables.
	function __get( $var ) {
		return ( isset( $this->$var ) ) ? $this->$var : null;
	}

	public function init() {

		$this->default_settings = array(
			'project_type' => false,
		);

		$this->settings = wp_parse_args( (array) get_option( self::OPTION ), $this->default_settings );
	}

	public function admin_init() {
		if ( ! empty( $_GET['developer_plugin_reset'] ) && current_user_can( 'manage_options' ) ) {
			delete_option( self::OPTION );
		}

		$this->recommended_plugins = array(
			'postmatic' => array(
				'project_type' => 'all',
				'name'         => esc_html__( 'Postmatic - Email comment subscriptions for huge increases in commenting.', 'ucp-developer' ),
				'active'       => class_exists( 'postmatic' ),
			),
			'epoch' => array(
				'project_type' => 'all',
				'name'         => esc_html__( 'Epoch - Replace your comment form with one that is fast, beautiful, and fun.', 'ucp-developer' ),
				'active'       => function_exists( 'epoch' ),
			),
			'postmatic-social-commenting' => array(
				'project_type' => 'all',
				'name'         => esc_html__( 'Postmatic Social Commenting - Let users comment using their twitter/facebook/google/wordpress profiles.', 'ucp-developer' ),
				'active'       => function_exists( 'postmatic-social-commenting' ),
			),
			'akismet' => array(
				'project_type' => 'all',
				'name'         => esc_html__( 'Akismet - The standard for anti-spam', 'ucp-developer' ),
				'active'       => class_exists( 'Akismet' ),
			),
			'basic-comment-quicktags' => array(
				'project_type' 	=> 'all',
				'name' 		=> esc_html__( 'Basic Comment Quicktags - Add minimal bold/italic/link buttons to your comment form.', 'ucp-developer' ),
				'active'	=> function_exists( 'BasicCommentsQuicktagsHELF' ),
			),
			'show-comment-policy' => array(
				'project_type' => 'all',
				'name'         => esc_html__( 'Show Comment Policy - Set behavioural expectations before users leave a comment.', 'ucp-developer' ),
				'active'       => class_exists( 'show-comment-policy' ),
			),
			'goodbye-captcha' => array(
				'project_type' => 'all',
				'name'         => esc_html__( 'WPBruiser - Front-end antispam plugin that keeps bots from submitting the comment form.', 'ucp-developer' ),
				'active'       => class_exists( 'goodbye-captcha' ),
			),
			'crowd-control' => array(
				'project_type' => 'large',
				'name'         => esc_html__( 'Crowd Control', 'ucp-developer' ),
				'active'       => class_exists( 'postmatic-crowd' ),
			),
			'jetpack' => array(
				'project_type' => 'large',
				'name'         => esc_html__( 'Jetpack', 'ucp-developer' ),
				'active'       => class_exists( 'Jetpack' ),
			),
			'polldaddy' => array(
				'project_type' => 'large',
				'name'         => esc_html__( 'Polldaddy Polls & Ratings', 'ucp-developer' ),
				'active'       => class_exists( 'WP_Polldaddy' ),
			),
			'monster-widget' => array(
				'project_type' => 'all',
				'name'         => esc_html__( 'Monster Widget', 'ucp-developer' ),
				'active'       => class_exists( 'Monster_Widget' ),
			),
			'user-switching' => array(
				'project_type' => 'all',
				'name'         => esc_html__( 'User Switching', 'ucp-developer' ),
				'active'       => class_exists( 'user_switching' ),
			),
			'piglatin' => array(
				'project_type' 	=> array( 'small', 'wporg' ),
				'name'		=> esc_html__( 'Pig Latin', 'ucp-developer' ),
				'active'	=> class_exists( 'PigLatin' ),
			),

			// Theme Developer
			'rtl-tester' => array(
				'project_type' => 'small',
				'name'         => esc_html__( 'RTL Tester', 'ucp-developer' ),
				'active'       => class_exists( 'RTLTester' ),
			),
			'regenerate-thumbnails' => array(
				'project_type' => 'small',
				'name'         => esc_html__( 'Regenerate Thumbnails', 'ucp-developer' ),
				'active'       => class_exists( 'RegenerateThumbnails' ),
			),
			'simply-show-ids' => array(
				'project_type' => 'small',
				'name'         => esc_html__( 'Simply Show IDs', 'ucp-developer' ),
				'active'       => function_exists( 'ssid_add' ),
			),
			'theme-test-drive' => array(
				'project_type' => 'small',
				'name'         => esc_html__( 'Theme Test Drive', 'ucp-developer' ),
				'active'       => function_exists( 'TTD_filters' ),
				'filename'     => 'themedrive.php',
			),
			'theme-check' => array(
				'project_type' => 'small',
				'name'         => esc_html__( 'Theme Check', 'ucp-developer' ),
				'active'       => class_exists( 'ThemeCheckMain' ),
			),
		);

		register_setting( self::OPTION, self::OPTION, array( $this, 'settings_validate' ) );

		wp_register_script( 'ucp-developer', plugins_url( 'developer.js', __FILE__ ), array( 'jquery' ), self::VERSION );
		$strings = array(
			'settings_slug'  => self::PAGE_SLUG,
			'go_to_step_2'   => ( current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugins' ) && 'direct' == get_filesystem_method() ) ? 'yes' : 'no',
			'lightbox_title' => __( 'Ultimate Comment Pack', 'ucp-developer' ),
			'saving'         => __( 'Saving...', 'ucp-developer' ),
			'installing'     => '' . esc_html__( 'Installing...', 'ucp-developer' ),
			'installed'      => __( '&#10003; Activated', 'ucp-developer' ),
			'activating'     => '' . esc_html__( 'Activating...', 'ucp-developer' ),
			'activated'      => __( '&#10003; Activated', 'ucp-developer' ),
			'error'          => __( 'Error!', 'ucp-developer' ),
			'ACTIVE'      	 => __( 'ACTIVE', 'ucp-developer' ),
			'INSTALLED'      => __( 'INSTALLED', 'ucp-developer' ),
			'ERROR'          => __( 'ERROR!', 'ucp-developer' ),
		);
		wp_localize_script( 'ucp-developer', 'ucp_developer_i18n', $strings );

		wp_register_style( 'ucp-developer', plugins_url( 'developer.css', __FILE__ ), array(), self::VERSION );

		// Handle the submission of the lightbox form if step 2 won't be shown
		if ( ! empty( $_POST['action'] ) && 'ucp_developer_lightbox_step_1' == $_POST['action'] && ! empty( $_POST['ucp_developer_project_type'] ) && check_admin_referer( 'ucp_developer_lightbox_step_1' ) ) {
			$this->save_project_type( $_POST['ucp_developer_project_type'] );
			add_settings_error( 'general', 'settings_updated', __( 'Settings saved.' ), 'updated' );
		}

		if ( ! get_option( self::OPTION ) ) {
			if ( ! empty( $_GET['ucpdev_errorsaving'] ) ) {
				add_settings_error( self::PAGE_SLUG, self::PAGE_SLUG . '_error_saving', __( 'Error saving settings. Please try again.', 'ucp-developer' ) );
			} elseif ( ! is_network_admin() && current_user_can( 'manage_options' ) ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'load_lightbox_scripts_and_styles' ) );
				add_action( 'admin_footer', array( $this, 'output_setup_box_html' ) );
			}
		}
	}

	public function register_settings_page() {
		add_management_page( esc_html__( 'Ultimate Comment Pack', 'ucp-developer' ), esc_html__( 'Ultimate Comment Pack', 'ucp-developer' ), 'manage_options', self::PAGE_SLUG, array( $this, 'settings_page' ) );
	}

	public function add_node_to_admin_bar( $wp_admin_bar ) {

		if ( !current_user_can( 'manage_options' ) )
			return;

		$wp_admin_bar->add_node( array(
			'id'     => self::PAGE_SLUG,
			'title'  => esc_html__( 'Developer', 'ucp-developer' ),
			'parent' => 'top-secondary', // Off on the right side
			'href'   => admin_url( 'settings.php?page=' . self::PAGE_SLUG ),
			'meta'   => array(
			'title'  => esc_html__( 'View the Ultimate Comment Pack settings and status page', 'ucp-developer' ),
			),
		) );
	}

	public function load_settings_page_script_and_style( $hook_suffix ) {
		if ( 'setttings_page_' . self::PAGE_SLUG != $hook_suffix )
			return;

		wp_enqueue_script( 'ucp-developer' );
		wp_enqueue_style( 'ucp-developer' );
	}

	public function load_lightbox_scripts_and_styles() {
		wp_enqueue_script( 'colorbox', plugins_url( 'colorbox/jquery.colorbox-min.js', __FILE__ ), array( 'jquery' ), '1.3.19' );
		wp_enqueue_style( 'ucp-developer-colorbox', plugins_url( 'colorbox/colorbox.css', __FILE__ ), array(), '1.3.19' );

		wp_enqueue_script( 'ucp-developer' );
		wp_enqueue_style( 'ucp-developer' );
	}

	public function output_setup_box_html() {
?>

		<div style="display:none">
			<div id="ucp-developer-setup-dialog-step-1" class="ucp-developer-dialog">
				<strong><?php esc_html_e( "Tell us a little bit about your site. We'll recommend the best free commenting plugins for your community.", 'ucp-developer' ); ?></strong>

				<p><?php esc_html_e( 'What best describes the kinds of commenting you hope to achieve?', 'ucp-developer' ); ?></p>

				<form id="ucp-developer-setup-dialog-step-1-form" action="settings.php?page=ucp_developer" method="post">
					<?php wp_nonce_field( 'ucp_developer_lightbox_step_1' ); ?>
					<input type="hidden" name="action" value="ucp_developer_lightbox_step_1" />

					<?php $i = 0; ?>
					<?php foreach ( $this->get_project_types() as $project_slug => $project_description ) : ?>
						<?php $i++; ?>
						<p>
							<label>
								<input type="radio" name="ucp_developer_project_type" value="<?php echo esc_attr( $project_slug ); ?>" <?php checked( $i, 1 ); ?> />
								<?php echo $project_description; ?>
							</label>
						</p>
					<?php endforeach; ?>

					<?php submit_button( null, 'primary', 'ucp-developer-setup-dialog-step-1-submit' ); ?>
				</form>
			</div>
			<div id="ucp-developer-setup-dialog-step-2" class="ucp-developer-dialog">
				<!-- This gets populated via AJAX -->
			</div>
		</div>

		<script type="text/javascript">ucp_developer_lightbox();</script>
<?php
	}

	public function ajax_handler( $action ) {
		$action = isset( $_POST['action'] ) ? $_POST['action'] : $action;
		switch ( $action ) {

			case 'ucp_developer_lightbox_step_1':
				check_ajax_referer( 'ucp_developer_lightbox_step_1' );

				if ( empty( $_POST['ucp_developer_project_type'] ) )
					die( '-1' );

				$this->save_project_type( $_POST['ucp_developer_project_type'] );

				$to_install_or_enable = 0;

				$recommended_plugins = $this->get_recommended_plugins();

				foreach ( $recommended_plugins as $plugin_slug => $plugin_details ) {
					if ( ! $plugin_details['active'] ) {
						$to_install_or_enable++;
					}
				}

				// If no plugins to take action on, head to the settings page
				if ( ! $to_install_or_enable )
					die( 'redirect' );

				echo '<strong>' . esc_html__( 'Plugins', 'ucp-developer' ) . '</strong>';

				echo '<p>' . esc_html__( 'Here is a curated list of commenting plugins that would work well for your site:', 'ucp-developer' ) . '</p>';

				echo '<table class="recommended-plugins">';

					foreach ( $recommended_plugins as $plugin_slug => $plugin_details ) {
						if ( $plugin_details['active'] )
							continue;

						echo '<tr>';

						$details = $this->get_plugin_details( $plugin_slug );

						if ( is_wp_error( $details ) )
							$details = array();

						$plugin_details = array_merge( (array) $details, array( 'slug' => $plugin_slug ), $plugin_details );

						echo '<td><strong>' . $plugin_details['name'] . '</strong></td>';

						echo '<td>';
						
						if ( ! empty( $plugin_details['short_description'] ) )
								echo '<span class="description">' . esc_html__( $plugin_details['short_description'] ) . '</span>';

						if ( $this->is_recommended_plugin_installed( $plugin_slug ) ) {
							$path = $this->get_path_for_recommended_plugin( $plugin_slug );

							echo '<button type="button" class="ucp-developer-button-activate" data-path="' . esc_attr( $path ) . '" data-nonce="' . wp_create_nonce( 'ucp_developer_activate_plugin_' . $path ) . '">' . esc_html__( 'Activate', 'ucp-developer' ) . '</button>';
						} else {
							echo '<button type="button" class="ucp-developer-button-install" data-pluginslug="' . esc_attr( $plugin_slug ) . '" data-nonce="' . wp_create_nonce( 'ucp_developer_install_plugin_' . $plugin_slug ) . '">' . esc_html__( 'Install', 'ucp-developer' ) . '</button>';
						}

					

						echo '</td>';

						echo '</tr>';
					}

				echo '<tr><td colspan="2"><button type="button" class="ucp-developer-button-close">' . esc_html__( 'All set!', 'ucp-developer' ) . '</button></td></tr>';

				echo '</table>';

				echo '<script type="text/javascript">ucp_developer_bind_events();</script>';

				exit();

			case 'ucp_developer_install_plugin':
				if ( empty( $_POST['plugin_slug'] ) )
					die( __( 'ERROR: No slug was passed to the AJAX callback.', 'ucp-developer' ) );

				check_ajax_referer( 'ucp_developer_install_plugin_' . $_POST['plugin_slug'] );

				if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) )
					die( __( 'ERROR: You lack permissions to install and/or activate plugins.', 'ucp-developer' ) );

				include_once ( ABSPATH . 'wp-admin/includes/plugin-install.php' );

				$api = plugins_api( 'plugin_information', array( 'slug' => $_POST['plugin_slug'], 'fields' => array( 'sections' => false ) ) );

				if ( is_wp_error( $api ) )
					die( sprintf( __( 'ERROR: Error fetching plugin information: %s', 'ucp-developer' ), $api->get_error_message() ) );

				$upgrader = new Plugin_Upgrader( new Automattic_Developer_Empty_Upgrader_Skin( array(
					'nonce'  => 'install-plugin_' . $_POST['plugin_slug'],
					'plugin' => $_POST['plugin_slug'],
					'api'    => $api,
				) ) );

				$install_result = $upgrader->install( $api->download_link );

				if ( ! $install_result || is_wp_error( $install_result ) ) {
					// $install_result can be false if the file system isn't writeable.
					$error_message = __( 'Please ensure the file system is writeable', 'ucp-developer' );

					if ( is_wp_error( $install_result ) )
						$error_message = $install_result->get_error_message();

					die( sprintf( __( 'ERROR: Failed to install plugin: %s', 'ucp-developer' ), $error_message ) );
				}

				$activate_result = activate_plugin( $this->get_path_for_recommended_plugin( $_POST['plugin_slug'] ) );

				if ( is_wp_error( $activate_result ) )
					die( sprintf( __( 'ERROR: Failed to activate plugin: %s', 'ucp-developer' ), $activate_result->get_error_message() ) );

				exit( '1' );

			case 'ucp_developer_activate_plugin':
				if ( empty( $_POST['path'] ) )
					die( __( 'ERROR: No slug was passed to the AJAX callback.', 'ucp-developer' ) );

				check_ajax_referer( 'ucp_developer_activate_plugin_' . $_POST['path'] );

				if ( ! current_user_can( 'activate_plugins' ) )
					die( __( 'ERROR: You lack permissions to activate plugins.', 'ucp-developer' ) );

				$activate_result = activate_plugin( $_POST['path'] );

				if ( is_wp_error( $activate_result ) )
					die( sprintf( __( 'ERROR: Failed to activate plugin: %s', 'ucp-developer' ), $activate_result->get_error_message() ) );

				exit( '1' );
		}

		// Unknown action
		die( '-1' );
	}

	public function settings_page() {
		add_settings_section( 'ucp_developer_main', esc_html__( 'Main Configuration', 'ucp-developer' ), '__return_false', self::PAGE_SLUG . '_settings' );
		add_settings_field( 'ucp_developer_project_type', esc_html__( 'Project Type', 'ucp-developer' ), array( $this, 'settings_field_radio' ), self::PAGE_SLUG . '_settings', 'ucp_developer_main', array(
			'name'        => 'project_type',
			'description' => '',
			'options'     => $this->get_project_types(),
		) );

		echo '<script type="text/javascript">
			jQuery(function( $ ) {
				ucp_developer_bind_settings_events();
			});
		</script>';

		// Plugins
		add_settings_section( 'ucp_developer_plugins', esc_html__( 'Plugins', 'ucp-developer' ), array( $this, 'settings_section_plugins' ), self::PAGE_SLUG . '_status' );

		wp_enqueue_script( 'plugin-install' );

		add_thickbox();

		$recommended_plugins = $this->get_recommended_plugins();

		foreach ( $recommended_plugins as $plugin_slug => $plugin_details ) {
			$details = $this->get_plugin_details( $plugin_slug );

			if ( is_wp_error( $details ) )
				$details = array();

			$plugin_details = array_merge( (array) $details, array( 'slug' => $plugin_slug ), $plugin_details );

			$label = '<strong>' . esc_html( $plugin_details['name'] ) . '</strong>';

			$label .= '<br /><a href="' . self_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=' . $plugin_slug .
								'&amp;TB_iframe=true&amp;width=600&amp;height=750' ) . '" class="thickbox" title="' .
								esc_attr( sprintf( __( 'More information about %s' ), $plugin_details['name'] ) ) . '">' . __( 'Details' ) . '</a>';

			add_settings_field( 'ucp_developer_plugin_' . $plugin_slug, $label, array( $this, 'settings_field_plugin' ), self::PAGE_SLUG . '_status', 'ucp_developer_plugins', $plugin_details );
		}

		// Constants
		add_settings_section( 'ucp_developer_constants', esc_html__( 'Constants', 'ucp-developer' ), array( $this, 'settings_section_constants' ), self::PAGE_SLUG . '_status' );

		$recommended_constants = $this->get_recommended_constants();

		foreach ( $recommended_constants as $constant => $constant_details ) {
			add_settings_field( 'ucp_developer_constant_' . $constant, $constant, array( $this, 'settings_field_constant' ), self::PAGE_SLUG . '_status', 'ucp_developer_constants', array(
				'constant'    => $constant,
				'description' => $constant_details['description'],
			) );
		}

		// Settings
		add_settings_section( 'ucp_developer_settings', esc_html__( 'Settings', 'ucp-developer' ), array( $this, 'settings_section_settings' ), self::PAGE_SLUG . '_status' );
		add_settings_field( 'ucp_developer_setting_permalink_structure', esc_html__( 'Pretty Permalinks', 'ucp-developer' ), array( $this, 'settings_field_setting_permalink_structure' ), self::PAGE_SLUG . '_status', 'ucp_developer_settings' );
		if ( 'large' == $this->settings['project_type'] ) {
			add_settings_field( 'ucp_developer_setting_development_version', esc_html__( 'Development Version', 'ucp-developer' ), array( $this, 'settings_field_setting_development_version' ), self::PAGE_SLUG . '_status', 'ucp_developer_settings' );
			add_settings_field( 'ucp_developer_setting_shared_plugins', esc_html__( 'Shared Plugins', 'ucp-developer' ), array( $this, 'settings_field_setting_shared_plugins' ), self::PAGE_SLUG . '_status', 'ucp_developer_settings' );
		}

		// Resources
		add_settings_section( 'ucp_developer_resources', esc_html__( 'Resources', 'ucp-developer' ), array( $this, 'settings_section_resources' ), self::PAGE_SLUG . '_status' );

		add_settings_field( 'ucp_developer_setting_codex', esc_html__( 'Codex', 'ucp-developer' ), array( $this, 'settings_field_setting_resource_codex' ), self::PAGE_SLUG . '_status', 'ucp_developer_resources' );

		if ( 'large' == $this->settings['project_type'] )
			add_settings_field( 'ucp_developer_setting_vip_docs', esc_html__( 'VIP Docs', 'ucp-developer' ), array( $this, 'settings_field_setting_resource_vip_docs' ), self::PAGE_SLUG . '_status', 'ucp_developer_resources' );

		if ( in_array( $this->settings['project_type'], array( 'small', 'large' ) ) )
			add_settings_field( 'ucp_developer_setting_starter_themes', esc_html__( 'Starter Themes', 'ucp-developer' ), array( $this, 'settings_field_setting_resource_starter_themes' ), self::PAGE_SLUG . '_status', 'ucp_developer_resources' );

		# Add more sections and fields here as needed
?>

		<div class="wrap">

		<?php screen_icon( 'comments' ); ?>

		<h2><?php esc_html_e( 'Ultimate Comment Pack', 'ucp-developer' ); ?></h2>

		<form action="options.php" method="post">

			<?php settings_fields( self::OPTION ); // matches value from register_setting() ?>

			<?php do_settings_sections( self::PAGE_SLUG . '_settings' ); // matches values from add_settings_section/field() ?>

			<?php submit_button(); ?>

			<?php do_settings_sections( self::PAGE_SLUG . '_status' ); ?>
		</form>

		</div>
<?php
	}

	public function settings_field_radio( $args ) {
		if ( empty( $args['name'] ) || ! is_array( $args['options'] ) )
			return false;

		$selected = ( isset( $this->settings[ $args['name'] ] ) ) ? $this->settings[ $args['name'] ] : '';

		foreach ( (array) $args['options'] as $value => $label )
			echo '<p><label><input type="radio" name="ucp_developer[' . esc_attr( $args['name'] ) . ']" value="' . esc_attr( $value ) . '"' . checked( $value, $selected, false ) . '> ' . $label . '</input></label></p>';

		if ( ! empty( $args['description'] ) )
			echo ' <p class="description">' . $args['description'] . '</p>';
	}

	public function settings_field_select( $args ) {
		if ( empty( $args['name'] ) || ! is_array( $args['options'] ) )
			return false;

		$selected = ( isset( $this->settings[ $args['name'] ] ) ) ? $this->settings[ $args['name'] ] : '';

		echo '<select name="ucp_developer[' . esc_attr( $args['name'] ) . ']">';

		foreach ( (array) $args['options'] as $value => $label )
			echo '<option value="' . esc_attr( $value ) . '"' . selected( $value, $selected, false ) . '>' . $label . '</option>';

		echo '</select>';

		if ( ! empty( $args['description'] ) )
			echo ' <p class="description">' . $args['description'] . '</p>';
	}

	public function settings_section_plugins() {
		echo '<p>' . esc_html__( 'Here is a list of recommended plugins for your site:', 'ucp-developer' ) . '</p>';
	}

	public function settings_field_plugin( $args ) {
		if ( $args['active'] ) {
			echo '<span class="ucp-developer-active">' . esc_html__( 'ACTIVE', 'ucp-developer' ) . '</span>';
		} elseif ( $this->is_recommended_plugin_installed( $args['slug'] ) ) {
			// Needs to be activated
			if ( current_user_can('activate_plugins') ) {
				$path = $this->get_path_for_recommended_plugin( $args['slug'] );
				echo '<a class="ucp-developer-notactive ucp-developer-button-activate" href="' . esc_url( wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . $path ), 'activate-plugin_' . $path ) ) . '" data-path="' . esc_attr( $path ) . '" data-nonce="' . wp_create_nonce( 'ucp_developer_activate_plugin_' . $path ) . '" title="' . esc_attr__( 'Click here to activate', 'ucp-developer' ) . '">' . esc_html__( 'INACTIVE', 'ucp-developer' ) . ' - <em>' . esc_html__( 'Click to Activate', 'ucp-developer' ) . '</em></a>';
			} else {
				echo '<span class="ucp-developer-notactive">' . esc_html__( 'INACTIVE', 'ucp-developer' ) . '</span>';
			}
		} else {
			// Needs to be installed
			if ( current_user_can('install_plugins') ) {
				echo '<a class="ucp-developer-notactive ucp-developer-button-install" href="' . esc_url( wp_nonce_url( admin_url( 'update.php?action=install-plugin&plugin=' . $args['slug'] ), 'install-plugin_' . $args['slug'] ) ) . '" data-pluginslug="' . esc_attr( $args['slug'] ) . '" data-nonce="' . wp_create_nonce( 'ucp_developer_install_plugin_' . $args['slug'] ) . '" title="' . esc_attr__( 'Click here to install', 'ucp-developer' ) . '">' . esc_html__( 'NOT INSTALLED', 'ucp-developer' ) . ' - <em>' . esc_html__( 'Click to Install', 'ucp-developer' ) . '</em></a>';
			} else {
				echo '<span class="ucp-developer-notactive">' . esc_html__( 'NOT INSTALLED', 'ucp-developer' ) . '</span>';
			}
		}

		if ( ! empty( $args['short_description'] ) )
			echo '<br /><span class="description">' . $args['short_description']  . '</span>';
	}

	public function settings_section_constants() {
		echo '<p>' . __( 'We recommend you set the following constants to <code>true</code> in your <code>wp-config.php</code> file. <a href="http://codex.wordpress.org/Editing_wp-config.php" target="_blank">Need help?</a>', 'ucp-developer' ) . '</p>';
	}

	public function settings_field_constant( $args ) {
		if ( defined( $args['constant'] ) && constant( $args['constant'] ) ) {
			echo '<span class="ucp-developer-active">' . esc_html__( 'SET', 'ucp-developer' ) . '</span>';
		} else {
			echo '<span class="ucp-developer-notactive">' . esc_html__( 'NOT SET', 'ucp-developer' ) . '</span>';
		}

		if ( ! empty( $args['description'] ) )
			echo '<br /><span class="description">' . $args['description'] . '</span>';
	}


	public function settings_section_settings() {
		echo '<p>' . esc_html__( 'We recommend the following settings and configurations.', 'ucp-developer' ) . '</p>';
	}

	public function settings_field_setting_permalink_structure() {
		if ( get_option( 'permalink_structure' ) ) {
			echo '<span class="ucp-developer-active">' . esc_html__( 'ENABLED', 'ucp-developer' ) . '</span>';
		} else {
			echo '<a class="ucp-developer-notactive" href="' . admin_url( 'options-permalink.php' ) . '">' . esc_html__( 'DISABLED', 'ucp-developer' ) . '</a> ' . __( '<a href="http://codex.wordpress.org/Using_Permalinks" target="_blank">Need help?</a>', 'ucp-developer' );
		}
	}

	public function settings_field_setting_development_version() {
		if ( self::is_dev_version() ) {
			echo '<span class="ucp-developer-active">' . esc_html__( 'ENABLED', 'ucp-developer' ) . '</span>';
		} else {
			echo '<a href="'. network_admin_url( 'update-core.php' ) .'" class="ucp-developer-notactive">' . esc_html__( 'DISABLED', 'ucp-developer' ) . '</a>';
		}
	}

	public function settings_field_setting_shared_plugins() {
		if ( file_exists( WP_CONTENT_DIR . '/themes/vip' ) && file_exists( WP_CONTENT_DIR . '/themes/vip/plugins' ) ) {
			echo '<span class="ucp-developer-active">' . esc_html__( 'ENABLED', 'ucp-developer' ) . '</span>';
		} else {
			echo '<a href="http://vip.wordpress.com/documentation/development-environment/#plugins-and-helper-functions" class="ucp-developer-notactive">' . esc_html__( 'DISABLED', 'ucp-developer' ) . '</a>';
		}
	}

	public function settings_section_resources() {}

	public function settings_field_setting_resource_codex() {
		_e( "The <a href='http://codex.wordpress.org/Developer_Documentation'>Developer Documentation section</a> of the Codex offers guidelines and references for anyone wishing to modify, extend, or contribute to WordPress.", 'ucp-developer' );
	}

	public function settings_field_setting_resource_vip_docs() {
		_e( "The <a href='http://vip.wordpress.com/documentation/'>VIP Documentation</a> is a technical resource for developing sites on WordPress.com including best practices and helpful tips to help you code better, faster, and stronger.", 'ucp-developer' );
	}

	public function settings_field_setting_resource_starter_themes() {
		_e( "<a href='http://underscores.me'>_s (or underscores)</a>: a starter theme meant for hacking that will give you a \"1000-Hour Head Start\". Use it to create the next, most awesome WordPress theme out there.", 'ucp-developer' );
	}

	public function settings_validate( $raw_settings ) {
		$settings = array();

		$project_type_slugs = array_keys( $this->get_project_types() );
		if ( empty( $raw_settings['project_type'] ) || ! in_array( $raw_settings['project_type'], $project_type_slugs ) )
			$settings['project_type'] = current( $project_type_slugs );
		else
			$settings['project_type'] = $raw_settings['project_type'];

		return $settings;
	}

	public function save_project_type( $type ) {
		$settings = $this->settings;
		$settings['project_type'] = $type;

		$this->settings = $this->settings_validate( $settings );

		update_option( self::OPTION, $this->settings );
	}

	public function get_path_for_recommended_plugin( $slug ) {
		$filename = ( ! empty( $this->recommended_plugins[$slug]['filename'] ) ) ? $this->recommended_plugins[$slug]['filename'] : $slug . '.php';

		return $slug . '/' . $filename;
	}

	public function is_recommended_plugin_active( $slug ) {
		if ( empty( $this->recommended_plugins[$slug] ) )
			return false;

		return $this->recommended_plugins[$slug]['active'];
	}

	public function is_recommended_plugin_installed( $slug ) {
		if ( empty( $this->recommended_plugins[$slug] ) )
			return false;

		if ( $this->is_recommended_plugin_active( $slug ) || file_exists( WP_PLUGIN_DIR . '/' . $this->get_path_for_recommended_plugin( $slug ) ) )
			return true;

		return false;
	}

	/**
	 * Retrieve plugin information for a given $slug
	 *
	 * Note that this does not use plugins_api(), as the .org api does not return
	 * short descriptions in POST requests (that api endpoint is different from this one)
	 *
	 * @param string $slug The plugin slug
	 * @return object The response object containing plugin details
	 */
	public function get_plugin_details( $slug ){
		$cache_key = md5( 'ucp_developer_plugin_details_' . $slug );

		if ( false === ( $details = get_transient( $cache_key ) ) ) {
			$request = wp_remote_get( 'http://api.wordpress.org/plugins/info/1.0/' . esc_url( $slug ), array( 'timeout' => 15 ) );

			if ( is_wp_error( $request ) ) {
				$details = new WP_Error('ucp_developer_plugins_api_failed', __( 'An unexpected error occurred. Something may be wrong with WordPress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="http://wordpress.org/support/">support forums</a>.' ), $request->get_error_message() );
			} else {
				$details = maybe_unserialize( wp_remote_retrieve_body( $request ) );

				if ( ! is_object( $details ) && ! is_array( $details ) )
					$details = new WP_Error('ucp_developer_plugins_api_failed', __( 'An unexpected error occurred. Something may be wrong with WordPress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="http://wordpress.org/support/">support forums</a>.' ), wp_remote_retrieve_body( $request ) );
				else
					set_transient( $cache_key, $details, WEEK_IN_SECONDS );
			}
		}

		return $details;
	}

	/**
	 * Return an array of all plugins recommended for the current project type
	 *
	 * Only returns plugins that have been recommended for the project type defined
	 * in $this->settings['project_type']
	 *
	 * @return array An array of plugins recommended for the current project type
	 */
	public function get_recommended_plugins() {
		return $this->get_recommended_plugins_by_type( $this->settings['project_type'] );
	}

	/**
	 * Return an array of all plugins recommended for the given project type
	 *
	 * @param  string $type The project type to return plugins for
	 * @return array An associative array of plugins for the project type
	 */
	public function get_recommended_plugins_by_type( $type ) {
		$plugins_by_type = array();

		foreach( $this->recommended_plugins as $plugin_slug => $plugin_details ) {
			if ( ! $this->plugin_is_recommended_for_project_type( $plugin_slug, $type ) )
				continue;

			$plugins_by_type[ $plugin_slug ] = $plugin_details;
		}

		return $plugins_by_type;
	}

	/**
	 * Should the given plugin be recommended for the given project type?
	 *
	 * Determines whether or not a given $plugin_slug is recommended for a given $project_type
	 * by checking the project types defined for it
	 *
	 * @param  string $plugin_slug The plugin slug to check
	 * @param  string $project_type The project type to check the plugin against
	 * @return bool Boolean indicating if the plugin is recommended for the project type
	 */
	public function plugin_is_recommended_for_project_type( $plugin_slug, $project_type = null ) {
		if ( null == $project_type )
			$project_type = $this->settings['project_type'];

		$plugin_details = $this->recommended_plugins[ $plugin_slug ];

		if ( 'all' == $plugin_details['project_type'] )
			return true;

		return self::is_project_type( $plugin_details, $project_type );
	}

	/**
	 * Return an array of all constants recommended for the current project type
	 *
	 * Only returns constants that have been recommended for the project type defined
	 * in $this->settings['project_type']
	 *
	 * @return array An array of constants recommended for the current project type
	 */
	public function get_recommended_constants() {
		return $this->get_recommended_constants_by_type( $this->settings['project_type'] );
	}

	/**
	 * Return an array of all constants recommended for the given project type
	 *
	 * @param  string $type The project type to return constants for
	 * @return array An associative array of constants for the project type
	 */
	public function get_recommended_constants_by_type( $type ) {
		$constants_by_type = array();

		foreach( $this->recommended_constants as $constant => $constant_details ) {
			if ( ! $this->constant_is_recommended_for_project_type( $constant, $type ) )
				continue;

			$constants_by_type[ $constant ] = $constant_details;
		}

		return $constants_by_type;
	}

	/**
	 * Should the given constant be recommended for the given project type?
	 *
	 * Determines whether or not a given $constant is recommended for a given $project_type
	 * by checking the project types defined for it
	 *
	 * @param  string $constant The constant to check
	 * @param  string $project_type The project type to check the constant against
	 * @return bool Boolean indicating if the constant is recommended for the project type
	 */
	public function constant_is_recommended_for_project_type( $constant, $project_type = null ) {
		if ( null == $project_type )
			$project_type = $this->settings['project_type'];

		$constant_details = $this->recommended_constants[ $constant ];

		if ( 'all' == $constant_details['project_type'] )
			return true;

		return self::is_project_type( $constant_details, $project_type );
	}

	public function get_project_types() {
		return array(
			'wporg'       => __( 'I\'m setting up a brand new WordPress site and need the best commenting setup available.', 'ucp-developer' ),
			'small' => __( 'I run a small site with only a few comments per post, but hope to increase engagement.', 'ucp-developer' ),
			'large'   => __( 'I run a large site with dozens or hundreds of comments per post and need help managing them.', 'ucp-developer' ),
		);
	}

	private static function is_dev_version() {
		$cur = get_preferred_from_update_core();
		return isset( $cur->response ) && $cur->response == 'development';
	}

	private static function is_project_type( $project, $type ) {
		$project_type = $project['project_type'];

		if ( is_array( $project_type ) )
			return in_array( $type, $project_type );

		return $project_type == $type;
	}
}

$automattic_developer = new Automattic_Developer();
