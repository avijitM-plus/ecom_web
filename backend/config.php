<?php
/**
 * Admin Backend Configuration
 * RoboMart E-commerce Platform
 */

// Include main configuration
require_once __DIR__ . '/../config.php';

// Require admin authentication for all backend pages
require_admin();

// Admin-specific settings
define('ADMIN_PER_PAGE', 10);
define('ADMIN_TITLE', 'RoboMart Admin');
?>
