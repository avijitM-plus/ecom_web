<?php
/**
 * Logout Script
 * RoboMart E-commerce Platform
 */

require_once 'config.php';

// Destroy session and logout user
destroy_session();

// Redirect to home page
redirect('index.html');
?>
