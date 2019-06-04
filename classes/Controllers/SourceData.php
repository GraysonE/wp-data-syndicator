<?php


namespace DataSync\Controllers;

use DataSync\Controllers\Options;
use DataSync\Controllers\ConnectedSites;
use WP_REST_Request;
use WP_REST_Server;
use ACF_Admin_Tool_Export;
use stdClass;

class SourceData {

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		$registered = register_rest_route(
			DATA_SYNC_API_BASE_URL,
			'/source_data/push',
			array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( $this, 'push' ),
//					'permission_callback' => array( __NAMESPACE__ . '\Auth', 'permissions' ),
				)
			)
		);
	}

	public function push() {
		$source_data     = $this->consolidate();
		$json            = wp_json_encode( $source_data );
		$connected_sites = $source_data->connected_sites;

		foreach ( $connected_sites as $site ) {
			$auth                    = new Auth();
			$auth_response           = $auth->authenticate_site( $site->url );
			$authorization_validated = $auth->validate( $site->url, $auth_response );

			if ( $authorization_validated ) {
				$token    = json_decode( $auth_response )->token;
				$url      = trailingslashit( $site->url ) . 'wp-json/' . DATA_SYNC_API_BASE_URL . '/receive';
				$args     = array(
					'body'    => $json,
					'headers' => array(
						'Authorization' => 'Bearer ' . $token,
					),
				);
				$response = wp_remote_post( $url, $args );
//				if ( $response->is_error() ) {
//					// Convert to a WP_Error object.
//					$error = $response->as_error();
//					$message = $response->get_error_message();
//					$error_data = $response->get_error_data();
//					$status = isset( $error_data['status'] ) ? $error_data['status'] : 500;
//					wp_die( printf( '<p>An error occurred: %s (%d)</p>', $message, $error_data ) );
//				}

				$body = wp_remote_retrieve_body( $response );
				print_r( $body );
			}

		}

	}

	private function consolidate() {

		$options = Options::source()->get_data();

		$source_data                  = new stdClass();
		$source_data->options         = (array) $options;
		$source_data->connected_sites = (array) ConnectedSites::get_all()->get_data();
		$source_data->nonce           = (string) wp_create_nonce( 'data_push' );
		$source_data->posts           = (array) Posts::get( array_keys( $options->push_enabled_post_types ) );
		$source_data->acf             = (array) Posts::get_acf_fields(); // use acf_add_local_field_group() to install this array.

		return $source_data;


	}

}