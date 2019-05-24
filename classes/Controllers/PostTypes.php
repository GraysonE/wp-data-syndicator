<?php


namespace DataSync\Controllers;

use DataSync\Models\PostType;

class PostTypes {

	public function __construct( $receiver_options, $source_options ) {

		global $wp_post_types;
		$registered_receiver_cpts = array_keys( $wp_post_types );

		if ( $receiver_options->add_and_enable_new_cpts ) {
			foreach ( $source_options->push_enabled_post_types as $post_type ) {
				if ( ! in_array( $post_type, $registered_receiver_cpts ) ) {
					$this->save( (object) $post_type );
				}
			}
		}
	}

	private function save( object $data ) {
		if ( ! $this->table_exists() ) {
			PostType::create_db_table();
		}
		echo gettype($data);
		print_r($data);

		if ( in_array( 'id', array_keys( $data ) ) ) {
			PostType::update( $data );
		} else {
			$new_id = PostType::create( $data );
			if ( is_numeric( $new_id ) ) {
				$data['id'] = $new_id;
			}
		}
		$new_data[] = $data;


		return wp_parse_args( $new_data );
	}

	private function table_exists() {
		global $wpdb;
		$table_name = $wpdb->prefix . PostType::$table_name;
		return in_array( $table_name, $wpdb->tables );
	}

}