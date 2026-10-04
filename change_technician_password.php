<?php

session_start();

require_once('util/security.php');
require_once('model/Database.php');

// Only technicians are authorized to access this page.
Security::checkAuthority('technician');

$conn = Database::connect();

$employeeID = (int)($_SESSION['employee_id'] ?? 0);

$message = '';
$error = '';

// Process password change.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate password.
    if (strlen($newPassword) < 8) {

        $error = 'Password must be at least 8 characters long.';

    } elseif ($newPassword !== $confirmPassword) {

        $error = 'The passwords do not match.';

    } else {

        $hashedPassword = password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );

        $sql = "UPDATE employees
                SET password = ?
                WHERE employee_id = ?
                AND level = 'Technician'";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "si",
            $hashedPassword,
            $employeeID
        );

        if ($stmt->execute()) {

            $message = 'Password changed successfully.';

        } else {

            $error = 'Unable to change password.';
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Change Password - Customer Complaint Management System
    </title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <div class="container">

        <h1>Customer Complaint Management System</h1>

        <h2>Change Technician Password</h2>

        <?php if ($message !== ''): ?>

            <p>
                <?php echo htmlspecialchars($message); ?>
            </p>

        <?php endif; ?>

        <?php if ($error !== ''): ?>

            <p>
                <?php echo htmlspecialchars($error); ?>
            </p>

        <?php endif; ?>

        <form method="post">

            <p>
                <label for="new_password">
                    New Password:
                </label>
                <br>

                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    required
                >
            </p>

            <p>
                <label for="confirm_password">
                    Confirm New Password:
                </label>
                <br>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    required
                >
            </p>

            <p>
                <button type="submit">
                    Change Password
                </button>
            </p>

        </form>

        <p>
            <a href="technician.php">
                Return to Technician Page
            </a>
        </p>

    </div>

</body>

</html>