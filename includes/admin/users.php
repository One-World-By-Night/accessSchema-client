<?php

// File: includes/admin/users.php
// Text Domain: accessschema-client
// @vesion 1.0.0
// @author author
// Function: Define admin users page for AccessSchema client

defined('ABSPATH') || exit;

global $prefix;
$slug  = strtolower($prefix . 'client');
$label = defined($prefix . 'LABEL') ? constant($prefix . 'LABEL') : 'AccessSchema';

/**
 * Add a column to the Users table for AccessSchema roles.
 */
add_filter('manage_users_columns', function ($columns) use ($label) {
    $columns['accessschema_roles'] = esc_html($label . ' Roles');
    return $columns;
});

/**
 * Populate the AccessSchema roles column.
 */
add_filter('manage_users_custom_column', function ($output, $column_name, $user_id) use ($slug, $label) {
    if ($column_name !== 'accessschema_roles') return $output;

    $cache_key     = "{$slug}_accessschema_cached_roles";
    $timestamp_key = "{$slug}_accessschema_cached_roles_timestamp";

    $roles     = get_user_meta($user_id, $cache_key, true);
    $timestamp = get_user_meta($user_id, $timestamp_key, true);

    $base_url   = admin_url('users.php');
    $flush_url  = wp_nonce_url(
        add_query_arg([
            'action'  => 'flush_accessschema_cache',
            'user_id' => $user_id,
            'slug'    => $slug,
        ], $base_url),
        "flush_accessschema_{$user_id}_{$slug}"
    );
    $refresh_url = wp_nonce_url(
        add_query_arg([
            'action'  => 'refresh_accessschema_cache',
            'user_id' => $user_id,
            'slug'    => $slug,
        ], $base_url),
        "refresh_accessschema_{$user_id}_{$slug}"
    );

    $output = '<div class="accessschema-role-column"><div><strong>' . esc_html($label) . ':</strong> ';

    if (!is_array($roles) || empty($roles)) {
        $output .= '[None] <a href="' . esc_url($refresh_url) . '">[Request]</a>';
    } else {
        $time_display = $timestamp
            ? date_i18n('m/d/Y h:i a', intval($timestamp))
            : '[Unknown]';

        $output .= esc_html($time_display)
            . ' <a href="' . esc_url($flush_url) . '">[Flush]</a>'
            . ' <a href="' . esc_url($refresh_url) . '">[Refresh]</a>';
    }

    $output .= '</div></div>';
    return $output;
}, 10, 3);

/**
 * Handle flush and refresh actions scoped to each plugin instance.
 */
add_action('admin_init', function () use ($slug) {
    if (
        isset($_GET['action'], $_GET['user_id'], $_GET['slug']) &&
        current_user_can('manage_options')
    ) {
        $user_id = intval($_GET['user_id']);
        $action  = sanitize_key($_GET['action']);
        $slug_in = sanitize_key($_GET['slug']);

        // Prevent tampering across slugs
        if ($slug_in !== $slug) return;

        $cache_key     = "{$slug}_accessschema_cached_roles";
        $timestamp_key = "{$slug}_accessschema_cached_roles_timestamp";

        if ($action === 'flush_accessschema_cache') {
            check_admin_referer("flush_accessschema_{$user_id}_{$slug}");

            delete_user_meta($user_id, $cache_key);
            delete_user_meta($user_id, $timestamp_key);

            wp_redirect(add_query_arg(['message' => 'accessschema_cache_flushed'], admin_url('users.php')));
            exit;
        }

        if ($action === 'refresh_accessschema_cache') {
            check_admin_referer("refresh_accessschema_{$user_id}_{$slug}");

            $user = get_user_by('ID', $user_id);
            if ($user) {
                $result = apply_filters('accessschema_client_refresh_roles', null, $user, $slug);

                if (is_array($result) && isset($result['roles'])) {
                    update_user_meta($user_id, $cache_key, $result['roles']);
                    update_user_meta($user_id, $timestamp_key, time());

                    wp_redirect(add_query_arg(['message' => 'accessschema_cache_refreshed'], admin_url('users.php')));
                    exit;
                }
            }

            wp_redirect(add_query_arg(['message' => 'accessschema_cache_failed'], admin_url('users.php')));
            exit;
        }
    }
});

/**
 * Show admin notices after flush or refresh.
 */
add_action('admin_notices', function () {
    if (!isset($_GET['message'])) return;

    $message = sanitize_text_field($_GET['message']);
    $notice  = '';

    switch ($message) {
        case 'accessschema_cache_flushed':
            $notice = 'AccessSchema role cache flushed.';
            break;
        case 'accessschema_cache_refreshed':
            $notice = 'AccessSchema role cache refreshed.';
            break;
        case 'accessschema_cache_failed':
            $notice = 'Failed to refresh AccessSchema roles. Check plugin hook or API response.';
            break;
    }

    if ($notice) {
        echo '<div class="notice notice-info is-dismissible"><p>' . esc_html($notice) . '</p></div>';
    }
});