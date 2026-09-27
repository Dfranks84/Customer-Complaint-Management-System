<?php

// Security utility class for Week 4 authentication
// and authorization.
class Security
{
    // Require HTTPS when this method is used.
    public static function checkHTTPS()
    {
        if (!isset($_SERVER['HTTPS']) ||
            $_SERVER['HTTPS'] !== 'on') {

            echo "<h1>HTTPS is Required!</h1>";
            exit();
        }
    }

    // Log the current user out.
    public static function logout()
    {
        // Remove all session data.
        $_SESSION = array();

        // Destroy the current session.
        session_destroy();

        // Start a new session for the logout message.
        session_start();

        $_SESSION['logout_msg'] = 'Successfully logged out.';

        // Return to the project login page.
        header('Location: login.php');
        exit();
    }

    // Check whether the logged-in user has permission
    // to access a protected page.
    public static function checkAuthority($auth)
    {
        if (!isset($_SESSION[$auth]) || !$_SESSION[$auth]) {

            $_SESSION['logout_msg'] =
                'Current login unauthorized for this page.';

            header('Location: login.php');
            exit();
        }
    }
}
