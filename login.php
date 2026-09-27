<?php

session_start();

require_once('controller/UserController.php');

$error_message = '';

// Process the login form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {

        $error_message = 'Please enter your email and password.';

    } else {

        if (UserController::login($email, $password)) {

            // Send the user to the appropriate page
            // based on authorization level.
            if ($_SESSION['admin']) {
                header('Location: admin.php');
                exit();
            } elseif ($_SESSION['technician']) {
                header('Location: technician.php');
                exit();
            } elseif ($_SESSION['customer']) {
                header('Location: customer.php');
                exit();
            }

        } else {

            $error_message = 'Invalid email or password.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login - Customer Complaint Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="container">

        <h1>Customer Complaint Management System</h1>

        <h2>Login</h2>

        <?php if ($error_message !== ''): ?>

            <p><?php echo htmlspecialchars($error_message); ?></p>

        <?php endif; ?>

        <?php if (isset($_SESSION['logout_msg'])): ?>

            <p>
                <?php
                echo htmlspecialchars($_SESSION['logout_msg']);
                unset($_SESSION['logout_msg']);
                ?>
            </p>

        <?php endif; ?>

        <form method="post" action="login.php">

            <p>
                <label for="email">Email:</label><br>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                >
            </p>

            <p>
                <label for="password">Password:</label><br>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >
            </p>

            <p>
                <button type="submit">Login</button>
            </p>

        </form>

        <p>
            <a href="index.php">Return to Home</a>
        </p>

    </div>

</body>

</html>