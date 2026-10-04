<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';

/**
 * User Database Model
 * Handles database operations for application users.
 */
class UserDB
{
    // Get a user by e-mail address.
    public static function getUserByEmail($email)
    {
        $conn = Database::connect();

        $query = "SELECT UserID, Email, Password, UserLevel
                  FROM users
                  WHERE Email = ?";

        $statement = $conn->prepare($query);

        $statement->bind_param("s", $email);

        $statement->execute();

        $result = $statement->get_result();

        $row = $result->fetch_assoc();

        $statement->close();

        if ($row) {
            return new User(
                $row['Email'],
                $row['Password'],
                $row['UserLevel'],
                $row['UserID']
            );
        }

        return null;
    }

    // Create a new application user.
    public static function createUser(User $user)
    {
        $conn = Database::connect();

        $query = "INSERT INTO users
                  (Email, Password, UserLevel)
                  VALUES (?, ?, ?)";

        $statement = $conn->prepare($query);

        $email = $user->getEmail();
        $password = $user->getPassword();
        $userLevel = $user->getUserLevel();

        $statement->bind_param(
            "ssi",
            $email,
            $password,
            $userLevel
        );

        $result = $statement->execute();

        if ($result) {
            $user->setUserId($conn->insert_id);
        }

        $statement->close();

        return $result;
    }
}