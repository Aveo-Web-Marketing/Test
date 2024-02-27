<?php
/*
 * Plugin Name:       Test
 * Plugin URI:        https://aveo.dk/
 * Description:       Tester funktionalitet for Aveo
 * Version:           1.0.1
 * Author:            Aveo
 * Update URI:        https://aveo.dk/
 * Text Domain:       test
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

add_filter('site_transient_update_plugins', 'aveo_test_check_for_plugin_update');

function aveo_test_check_for_plugin_update($checked_data) {
    if (empty($checked_data->checked)) {
        error_log('BARSRE: No plugin data');
        return $checked_data;
    }

    $transient_name = 'aveo_test_update_check';
    $cached_response = get_transient($transient_name);

    if ($cached_response !== false) {
        error_log('BARSRE: Using cached response');
        // We have a cached response, use this instead of making a new API call
        $response = $cached_response;
    } else {
        // No cached response, make an API call
        $api_url = 'https://api.github.com/repos/Aveo-Web-Marketing/Test/releases/latest';
        $response = wp_remote_get($api_url);
        error_log('BARSRE: Making API call');
        if (is_wp_error($response)) {
            return $checked_data;
        }
        $response = json_decode(wp_remote_retrieve_body($response), true);
        // Cache the response for a certain period (e.g., 1 hours)
        set_transient($transient_name, $response, 1 * HOUR_IN_SECONDS);
    }

    error_log('BARSRE: Response: ' . print_r($response, true));

    $latest_version = $response['tag_name'];
    $plugin_slug = plugin_basename(__FILE__);

    if (version_compare($checked_data->checked[$plugin_slug], $latest_version, '<')) {
        $checked_data->response[$plugin_slug] = [
            'url' => 'https://github.com/Aveo-Web-Marketing/Test',
            'slug' => $plugin_slug,
            'package' => $response['zipball_url'],
            'new_version' => $latest_version
        ];
    }

    return $checked_data;
}