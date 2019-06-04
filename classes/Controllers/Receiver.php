<?php


namespace DataSync\Controllers;


use WP_REST_Server;

class Receiver {

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		$registered = register_rest_route(
			DATA_SYNC_API_BASE_URL,
			'/receive',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'receive' ),
					'permission_callback' => array( __NAMESPACE__ . '\Auth', 'permissions' ),
				),
			)
		);
	}

	public function receive() {

		if ( isset( $_POST ) ) {
			$json_str    = file_get_contents( 'php://input' );
			$source_data = (object) json_decode( $json_str );
			Auth::verify_request( $source_data->nonce );
			$this->parse( $source_data );
		}

	}

	private function parse( object $source_data ) {

		print_r( $source_data );

		$source_options       = (object) $source_data->options;
		$connected_sites      = (object) $source_data->connected_sites;
		$receiver_options     = (object) Options::receiver()->get_data();
		$all_post_data        = (array) $source_data->posts;
		$post_types_to_import = array();

		PostTypes::add_new_cpts( $source_options );
		if ( $source_options->enable_new_cpts )
			PostTypes::save_options();

		foreach ( $receiver_options->enabled_post_types as $post_type ) {
			Posts::sync( $source_data->posts[$source_data->$post_type] );
		}

	}

}