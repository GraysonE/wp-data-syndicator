<?php

add_action('admin_init', 'add_wp_data_sync_options');

function add_wp_data_sync_options() {

	// SECTIONS
	add_settings_section( "wp_data_sync_global_settings", "", false, 'wp-data-sync-settings' );

	$source = get_option( 'source_site' );
	if ($source === '1') {
		add_settings_section( "wp_data_sync_source_settings", "Source Settings", false, 'wp-data-sync-settings' );
	} else if ($source === '0') {
		add_settings_section( "wp_data_sync_receiver_settings", "Receiver Settings", false, 'wp-data-sync-settings' );
	}



	///////////// OPTIONS /////////////////////////////

	/// SOURCE OR RECEIVER
	add_settings_field( "source_site", "Source or Receiver?", "display_source_input", 'wp-data-sync-settings', "wp_data_sync_global_settings" );
	register_setting( "wp_data_sync_global_settings", "source_site" );


	/////// SOURCE OPTIONS ///////

	// Connected Sites
		// blogname, Site ID, URL, date connected, remove button, connect new

	// Push new template file - cpt-templates.php
	add_settings_field( "push_template", "Push Template to Receivers", "display_push_template_button", 'wp-data-sync-settings', "wp_data_sync_source_settings" );

	// Select which Post Types syndicate to which Receiver sites.
	add_settings_field( "push_enabled_post_types", "Push-Enabled Post Types", "display_push_enabled_post_types", 'wp-data-sync-settings', "wp_data_sync_source_settings" );
	register_setting( "wp_data_sync_receiver_settings", "display_post_types_with_push_perm" );

	//	A “manual re-push” function will allow content to be selected at the Source and re-pushed to all receiving sites.  This is necessary to push out bulk uploaded / updated content.
	add_settings_field( "bulk_data_push", "Push All Data to Receivers", "display_bulk_data_push_button", 'wp-data-sync-settings', "wp_data_sync_source_settings" );

	// Error log
	add_settings_field( "error_log", "Error Log", "display_error_log", 'wp-data-sync-settings', "wp_data_sync_source_settings" );



	/////////// RECEIVER ///////////////////////////////

	// SECURITY TOKEN
//	add_settings_field( "security_token", "Security Token", "display_token", 'wp-data-sync-settings', "wp_data_sync_receiver_settings" );
//	register_setting( "wp_data_sync_receiver_settings", "security_token" );

	/// NOTIFIED USERS
	add_settings_field( "notified_users", "Notified Users", "display_notified_users", 'wp-data-sync-settings', "wp_data_sync_receiver_settings" );
	register_setting( "wp_data_sync_receiver_settings", "notified_users" );

	/// POST TYPES TO ACCEPT
	register_setting( "wp_data_sync_receiver_settings", "enabled_post_types");
	add_settings_field(
		'enabled_post_types', // id
		'Enabled Post Types', // title
		'display_post_types_to_accept', // callback
		'wp-data-sync-settings', // page
		'wp_data_sync_receiver_settings' // section
	);

	// POST TYPE PERMISSIONS
	$enabledPostTypes = get_option('enabled_post_types');
	if (count($enabledPostTypes) > 0) {
		foreach($enabledPostTypes as $post_type) {

			$post_type_object = get_post_type_object( $post_type );

			add_settings_field( $post_type_object->name."_perms", $post_type_object->label . " Permissions", "display_post_type_permissions_settings", 'wp-data-sync-settings', "wp_data_sync_receiver_settings", array($post_type_object) );
			register_setting( "wp_data_sync_receiver_settings", $post_type_object->name."_perms");

		}
	}


	// PULL DATA BUTTON
	add_settings_field( "pull_data", "Pull All Data From Source", "display_pull_data_button", 'wp-data-sync-settings', "wp_data_sync_receiver_settings" );


}