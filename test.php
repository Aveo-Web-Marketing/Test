<?php
/*
 * Plugin Name:       Test
 * Plugin URI:        https://aveo.dk/
 * Description:       Tester funktionalitet for Aveo
 * Version:           1.0.6
 * Author:            Aveo
 * Update URI:        https://aveo.dk/
 * Text Domain:       test
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

add_filter('site_transient_update_plugins', 'aveo_test_check_for_plugin_update', 100);

function aveo_test_check_for_plugin_update($checked_data) {
    if (empty($checked_data->checked)) { return $checked_data; } // Close if checked is empty

    // Check for transient
    $transient_name = 'aveo_test_update_check';
    $cached_response = get_transient($transient_name);
    if ($cached_response !== false) { return $checked_data; } // Close if transient is set
    set_transient($transient_name, 'not_empty', 60);

    // Get the latest version
    $api_url = 'https://api.github.com/repos/Aveo-Web-Marketing/Test/releases/latest';
    $access_token = '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';
    $args = array(
        'headers' => array(
            'Authorization' => 'token ' . $access_token,
            'User-Agent' => 'WordPress/' . $GLOBALS['wp_version'],
        ),
    );
    $response = wp_remote_get($api_url, $args);
    if (is_wp_error($response)) { return $checked_data; } // Close if response is error
    $response = json_decode(wp_remote_retrieve_body($response), true);
    error_log('BARSRE: Response: ' . print_r($response, true));

    // Check the version
    $plugin_slug = plugin_basename(__FILE__);
    $current_version = isset($checked_data->checked[$plugin_slug]) ? ltrim($checked_data->checked[$plugin_slug], 'v') : null;
    $latest_version = isset($response['tag_name']) ? ltrim($response['tag_name'], 'v') : null;

    // If a newer version is available, include it in the update array
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
