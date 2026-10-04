<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();

require_once('controller/UserController.php');
require_once('model/Database.php');

$error_message = '';

// Process the login form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $loginID = trim($_POST['login_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($loginID === '' || $password === '') {

        $error_message =
            'Please enter your Email/User ID and password.';

    } else {

        // First try the regular users table.
        if (UserController::login($loginID, $password)) {

            if ($_SESSION['admin']) {
                header('Location: admin.php');
                exit();
            } elseif ($_SESSION['customer']) {
                header('Location: customer.php');
                exit();
            }

        } else {

            // If regular login fails, check for a technician
            // using User ID and password.
            $conn = Database::connect();

            $sql = "SELECT employee_id, user_id, email, password
                    FROM employees
                    WHERE user_id = ?
                    AND level = 'Technician'";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $loginID);
            $stmt->execute();

            $result = $stmt->get_result();
            $technician = $result->fetch_assoc();

            $stmt->close();

            if (
                $technician &&
                password_verify(
                    $password,
                    $technician['password']
                )
            ) {

                $_SESSION['user_id'] =
                    $technician['user_id'];

                $_SESSION['employee_id'] =
                    $technician['employee_id'];

                $_SESSION['email'] =
                    $technician['email'];

                $_SESSION['user_level'] = 3;

                $_SESSION['admin'] = false;
                $_SESSION['customer'] = false;
                $_SESSION['technician'] = true;

                header('Location: technician.php');
                exit();

            } else {

                $error_message =
                    'Invalid Email/User ID or password.';
            }
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

            <p>
                <?php echo htmlspecialchars($error_message); ?>
            </p>

        <?php endif; ?>

        <?php if (isset($_SESSION['logout_msg'])): ?>

            <p>
                <?php
                echo htmlspecialchars(
                    $_SESSION['logout_msg']
                );

                unset($_SESSION['logout_msg']);
                ?>
            </p>

        <?php endif; ?>

        <form method="post" action="login.php">

            <p>
                <label for="login_id">
                    Email/User ID:
                </label><br>

                <input
                    type="text"
                    id="login_id"
                    name="login_id"
                    required
                >
            </p>

            <p>
                <label for="password">
                    Password:
                </label><br>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >
            </p>

            <p>
                <button type="submit">
                    Login
                </button>
            </p>

        </form>

        <p>
            <a href="index.php">
                Return to Home
            </a>
        </p>

    </div>

</body>

</html>