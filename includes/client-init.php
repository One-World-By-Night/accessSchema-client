<?php

/** File: includes/init.php
 * Text Domain: accessschema-client
 * @vesion 1.0.0
 * @author author
 * Function:  Porvide a single entry point to load all plugin components in standard and class-based structure
 */

defined('ABSPATH') || exit;

if (!defined('ASC_PREFIX')) {
    error_log('[AS] ERROR: ASC_PREFIX not defined.');
    return;
}

// === Build Slug and Label ===
$slug  = strtolower(str_replace('_', '-', ASC_PREFIX));         // e.g., 'OWBNBOARD' => 'owbnboard'
$label = ucwords(strtolower(str_replace('_', ' ', ASC_PREFIX))); // e.g., 'OWBNBOARD' => 'Owbnboard'

// === Register ===
function accessSchema_register_client_plugin($slug, $label) {
    add_filter('accessschema_registered_slugs', function ($slugs) use ($slug, $label) {
        if (!isset($slugs[$slug])) {
            $slugs[$slug] = $label;
        }
        return $slugs;
    });

    add_filter('accessschema_client_refresh_roles', function ($result, $user, $filter_slug) use ($slug) {
        if (!is_string($filter_slug)) {
            error_log("[AS] WARN: Non-string slug encountered in refresh_roles: " . print_r($filter_slug, true));
            return $result;
        }
        if ($filter_slug !== $slug) return $result;
        return accessSchema_refresh_roles_for_user($user, $slug);
    }, 10, 3);
}

accessSchema_register_client_plugin($slug, $label);

if (function_exists('accessSchema_client_register_render_admin')) {
    accessSchema_client_register_render_admin($slug, $label);
}