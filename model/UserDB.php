<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';

/**
 * User Database Model
 * Handles database operations for application users.
 */
class UserDB
{
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
}