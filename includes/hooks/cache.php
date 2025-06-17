<?php

// File: includes/hooks/cache.php
// Text Domain: accessschema-client
// @vesion 1.0.0
// @author author
// Function: Cache user roles on login

defined('ABSPATH') || exit;

add_action('wp_login', function ($user_login, $user) {
    if (!is_a($user, 'WP_User')) return;

    $registered_slugs = apply_filters('accessschema_registered_slugs', []);

    if (!is_array($registered_slugs)) return;

    foreach ($registered_slugs as $slug => $label) {
        $result = apply_filters('accessschema_client_refresh_roles', null, $user, $slug);

        if (is_array($result) && isset($result['roles'])) {
            update_user_meta($user->ID, "{$slug}_accessschema_cached_roles", $result['roles']);
            update_user_meta($user->ID, "{$slug}_accessschema_cached_roles_timestamp", time());
        }
    }
}, 10, 2);