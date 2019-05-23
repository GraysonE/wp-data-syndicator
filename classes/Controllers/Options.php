<?php

namespace DataSync\Controllers;

use WP_REST_Server;
use WP_REST_Request;

/**
 * Class Options
 * @package DataSync\Controllers
 *
 * Controller class for Options
 *
 * Doesn't need model because model is abstracted by WordPress core functionality
 */
class Options {

	/**
	 * Table prefix to save custom settings
	 *
	 * @var string
	 */
	protected static $table_prefix = 'data_sync_';
	/**
	 * Option key to save settings
	 *
	 * @var string
	 */
	protected static $option_key = 'setting';
	/**
	 * Default settings
	 *
	 * @var array
	 */
	protected static $defaults = array();

	public $view_namespace = 'DataSync';

	/**
	 * Options constructor.
	 */
	public function __construct() {
		require_once DATA_SYNC_PATH . 'views/admin/options/page.php';
		require_once DATA_SYNC_PATH . 'views/admin/options/fields.php';
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register' ] );
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Get saved settings
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public static function get( $settings ) {

//		$parameters  = $request->get_params();
//		$setting_key = array_keys( $parameters );
//		$setting     = $parameters[ $setting_key[0] ];
//
//		return rest_ensure_response( Settings::get( $setting ) );
//
//
//
//		if ( is_array( $settings ) ) {
//			foreach ( $settings as $key => $value ) {
//				$saved = get_option( $key, array() );
//			}
//		} else {
//			$saved = get_option( $settings, array() );
//		}
//
//		return wp_parse_args( $saved );


	}

	/**
	 * Save settings
	 *
	 *
	 * @param array $settings
	 */
	public static function save( WP_REST_Request $request ) {

		$key  = $request->get_url_params()[ self::$option_key ];
		$data = $request->get_json_params();

		$success = update_option( $key, $data );

		if ( $success ) {
			return wp_send_json_success();
		} else {
			$error = new Error();
			( $error ) ? $error->log( 'Settings NOT saved.' . "\n" ) : null;

			return wp_send_json_error();
		}
	}

	/**
	 * Add admin menu
	 */
	public function admin_menu() {
		add_options_page(
			'Data Sync',
			'Data Sync',
			'manage_options',
			'data-sync-settings',
			$this->view_namespace . '\data_sync_options_page'
		);
	}

	public function register_routes() {
		$registered = register_rest_route(
			DATA_SYNC_API_BASE_URL,
			'/settings/(?P<setting>[a-zA-Z-_]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get' ),
					'permission_callback' => array( __NAMESPACE__ . '\Auth', 'permissions' ),
					'args'                => array(
						'setting' => array(
							'description' => 'Setting key',
							'type'        => 'string',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'save' ),
					'permission_callback' => array( __NAMESPACE__ . '\Auth', 'permissions' ),
					'args'                => array(
						'setting' => array(
							'description' => 'Setting key',
							'type'        => 'string',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete' ),
					'permission_callback' => array( __NAMESPACE__ . '\Auth', 'permissions' ),
					'args'                => array(
						'setting' => array(
							'description' => 'Setting key',
							'type'        => 'string',
//							'validate_callback' => function ( $param, $request, $key ) {
//								return true;
//							},
						),
					),
				),
			)
		);
	}

	/**
	 * Add sections and options to Data Sync WordPress admin settings page.
	 * This also registers all options for updating.
	 */
	public function register() {
		add_settings_section( 'data_sync_settings', '', null, 'data-sync-settings' );

		add_settings_field( 'source_site', 'Source or Receiver?', $this->view_namespace . '\display_source_input', 'data-sync-settings', 'data_sync_settings' );
		register_setting( 'data_sync_settings', 'source_site' );

		$source = get_option( 'source_site' );

		if ( '1' === $source ) :

			add_settings_field( 'connected_sites', 'Connected Sites', $this->view_namespace . '\display_connected_sites', 'data-sync-settings', 'data_sync_settings' );

			add_settings_field( 'push_template', 'Push Template to Receivers', $this->view_namespace . '\display_push_template_button', 'data-sync-settings', 'data_sync_settings' );

			add_settings_field( 'bulk_data_push', 'Push All Data to Receivers', $this->view_namespace . '\display_bulk_data_push_button', 'data-sync-settings', 'data_sync_settings' );

			add_settings_field( 'push_enabled_post_types', 'Push-Enabled Post Types', $this->view_namespace . '\display_push_enabled_post_types', 'data-sync-settings', 'data_sync_settings' );
			register_setting( 'data_sync_settings', 'push_enabled_post_types' );

			add_settings_field( 'error_log', 'Error Log', $this->view_namespace . '\display_error_log', 'data-sync-settings', 'data_sync_settings' );
		elseif ( '0' === $source ) :

			add_settings_field( 'notified_users', 'Notified Users', $this->view_namespace . '\display_notified_users', 'data-sync-settings', 'data_sync_settings' );
			register_setting( 'data_sync_settings', 'notified_users' );

			register_setting( 'data_sync_settings', 'enabled_post_types' );
			add_settings_field(
				'enabled_post_types',
				'Enabled Post Types',
				$this->view_namespace . '\display_post_types_to_accept',
				'data-sync-settings',
				'data_sync_settings'
			);

			$enabled_post_types = get_option( 'enabled_post_types' );
			if ( ( $enabled_post_types ) && ( '' !== $enabled_post_types ) ) {
				if ( count( $enabled_post_types ) > 0 ) {
					foreach ( $enabled_post_types as $post_type ) {
						$post_type_object = get_post_type_object( $post_type );

						add_settings_field( $post_type_object->name . '_perms', $post_type_object->label . ' Permissions', $this->view_namespace . '\display_post_type_permissions_settings', 'data-sync-settings', 'data_sync_settings', array( $post_type_object ) );
						register_setting( 'data_sync_settings', $post_type_object->name . '_perms' );
					}
				}
			}

			add_settings_field( 'pull_data', 'Pull All Data From Source', $this->view_namespace . '\display_pull_data_button', 'data-sync-settings', 'data_sync_settings' );

		endif;
	}

}