<?php
/*
 * Plugin Name:       Test
 * Plugin URI:        https://aveo.dk/
 * Description:       Tester funktionalitet for Aveo
 * Version:           1.0.5
 * Author:            Aveo
 * Update URI:        https://aveo.dk/
 * Text Domain:       test
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

add_filter('site_transient_update_plugins', 'aveo_test_check_for_plugin_update', 100);

function aveo_test_check_for_plugin_update($checked_data) {
    error_log('BARSRE: Checking for updates');
    // delete_transient('aveo_test_update_check');
    if (empty($checked_data->checked)) {
        return $checked_data;
    }

    $transient_name = 'aveo_test_update_check';
    $cached_response = get_transient($transient_name);

    if ($cached_response !== false) {
        $response = $cached_response;
    } else {
        $api_url = 'https://api.github.com/repos/Aveo-Web-Marketing/Test/releases/latest';
        $response = wp_remote_get($api_url);
        if (is_wp_error($response)) {
            return $checked_data;
        }
        $response = json_decode(wp_remote_retrieve_body($response), true);
        // set_transient($transient_name, $response, 1 * HOUR_IN_SECONDS);
        set_transient($transient_name, $response, 60); // Expires after 60 seconds
    }

    $plugin_slug = plugin_basename(__FILE__);
    $current_version = isset($checked_data->checked[$plugin_slug]) ? ltrim($checked_data->checked[$plugin_slug], 'v') : null;
    $latest_version = isset($response['tag_name']) ? ltrim($response['tag_name'], 'v') : null;

    if ($latest_version && version_compare($current_version, $latest_version, '<')) {
        $object = new stdClass();
        $object->slug = $plugin_slug;
        $object->new_version = $latest_version;
        $object->url = 'https://github.com/Aveo-Web-Marketing/Test';
        $object->package = $response['zipball_url'];
        
        $checked_data->response[$plugin_slug] = $object;
    } else {
        error_log('BARSRE: No update available');
    }
    return $checked_data;
}