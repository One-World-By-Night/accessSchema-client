<?php

// File: includes/core/init.php
// Text Domain: accessschema-client
// @vesion 1.0.0
// @author author
// Function: Init core functionality for the plugin

defined( 'ABSPATH' ) || exit;

/** --- Require each core file once --- */
require_once __DIR__ . '/authorization.php';
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/client-api.php';