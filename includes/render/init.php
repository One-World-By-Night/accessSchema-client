<?php

// File: includes/render/init.php
// Text Domain: accessschema-client
// @vesion 1.0.0
// @author author
// Function: Init render functionality for the plugin

defined( 'ABSPATH' ) || exit;

/** --- Require each render file once --- */
require_once __DIR__ . '/admin.php';
require_once __DIR__ . '/editor.php';
require_once __DIR__ . '/listing.php';