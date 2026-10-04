<?php

require_once __DIR__ . '/../model/UserDB.php';

/**
 * User Controller
 * Handles user authentication for the application.
 */
class UserController
{
    public static function login($email, $password)
    {
        $user = UserDB::getUserByEmail($email);

        // Check that the user exists and the password matches.
        if ($user !== null && $user->getPassword() === $password) {

            // Store basic user information in the session.
            $_SESSION['user_id'] = $user->getUserId();
            $_SESSION['email'] = $user->getEmail();
            $_SESSION['user_level'] = $user->getUserLevel();

            // Set authorization based on user level.
            $_SESSION['admin'] = false;
            $_SESSION['technician'] = false;
            $_SESSION['customer'] = false;

            if ($user->getUserLevel() == 1) {
                $_SESSION['admin'] = true;
            } elseif ($user->getUserLevel() == 2) {
                $_SESSION['customer'] = true;
            } elseif ($user->getUserLevel() == 3) {
                $_SESSION['technician'] = true;
            }

            return true;
        }

        return false;
    }
}