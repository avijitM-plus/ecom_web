<?php
require_once 'config.php';
require_once 'includes/google-config.php';

if (isset($_GET['code'])) {
    try {
        $token = $google_client->fetchAccessTokenWithAuthCode($_GET['code']);

        if (!isset($token['error'])) {
            $google_client->setAccessToken($token['access_token']);

            // Verify ID Token to get user info without needing Google\Service\Oauth2
            $payload = $google_client->verifyIdToken($token['id_token']);

            if ($payload) {
                $google_id = $payload['sub'];
                $email = $payload['email'];
                $name = $payload['name'];
            } else {
                throw new Exception("Invalid ID Token");
            }

            // Check if user exists by google_id
            $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ?");
            $stmt->execute([$google_id]);
            $user = $stmt->fetch();

            if ($user) {
                // User exists with this Google ID - Log them in
                $role = isset($user['role']) ? $user['role'] : 'user';
                create_session($user['id'], $user['email'], $user['full_name'], $role);
                redirect('account.php');
            } else {
                // Check if email exists
                $existing_user = get_user_by_email($pdo, $email);

                if ($existing_user) {
                    // Link Google ID to existing account
                    $stmt = $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
                    $stmt->execute([$google_id, $existing_user['id']]);
                    
                    $role = isset($existing_user['role']) ? $existing_user['role'] : 'user';
                    create_session($existing_user['id'], $existing_user['email'], $existing_user['full_name'], $role);
                    redirect('account.php');
                } else {
                    // Create new user
                    // Generate a random password since they are using Google Login
                    $random_password = bin2hex(random_bytes(10));
                    $password_hash = password_hash($random_password, PASSWORD_DEFAULT);
                    
                    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, google_id) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $password_hash, $google_id]);
                    
                    $user_id = $pdo->lastInsertId();
                    create_session($user_id, $email, $name, 'user');
                    redirect('account.php');
                }
            }
        } else {
            // Handle error from Google
            $_SESSION['error'] = "Google Login Error: " . ($token['error'] ?? 'Unknown error');
            redirect('login.php');
        }
    } catch (Exception $e) {
        error_log("Google Login Exception: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred during Google Login.";
        redirect('login.php');
    }
} else {
    redirect('login.php');
}
