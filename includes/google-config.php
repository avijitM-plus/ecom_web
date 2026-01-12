<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Google Client Configuration
$google_client = new Google\Client();

// Set the Client ID
// REPLACE THIS WITH YOUR ACTUAL CLIENT ID
$google_client->setClientId(getenv('GOOGLE_CLIENT_ID') ?: '1006398211189-pnvmle3qjk54rvf21gtvm02f4vraionb.apps.googleusercontent.com');

// Set the Client Secret
// REPLACE THIS WITH YOUR ACTUAL CLIENT SECRET
$google_client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET') ?: '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');

// Set the Redirect URI
$google_client->setRedirectUri(SITE_URL . '/google-callback.php');

// Add scopes for email and profile
$google_client->addScope('email');
$google_client->addScope('profile');
