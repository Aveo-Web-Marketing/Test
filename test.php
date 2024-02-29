<?php
/*
 * Plugin Name:       Test
 * Plugin URI:        https://aveo.dk/
 * Description:       Tester funktionalitet for Aveo
 * Version:           1.0.4
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
        set_transient($transient_name, $response, 1 * HOUR_IN_SECONDS);
    }

    $plugin_slug = plugin_basename(__FILE__);
    $current_version = ltrim($checked_data->checked[$plugin_slug], 'v');
    $latest_version = ltrim($response['tag_name'], 'v');

    if (version_compare($current_version, $latest_version, '<')) {
        $checked_data->response[$plugin_slug] = [
            'id' => '0', // Not used by plugins hosted externally but required format
            'slug' => $plugin_slug,
            'plugin' => $plugin_slug,
            'new_version' => $latest_version,
            'url' => 'https://github.com/Aveo-Web-Marketing/Test', // Change to your plugin's info page if available
            'package' => $response['zipball_url'],
            'tested' => '5.9', // Update to the latest WordPress version you've tested with
        ];
    } else {
        error_log('BARSRE: No update available');
    }
    return $checked_data;
}
