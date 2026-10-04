<?php

session_start();

require_once('model/Customer.php');
require_once('model/User.php');
require_once('model/UserDB.php');
require_once('controller/CustomerController.php');

$error_message = '';
$success_message = '';

// Process the registration form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $streetAddress = trim($_POST['street_address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = strtoupper(trim($_POST['state'] ?? ''));
    $zipCode = trim($_POST['zip_code'] ?? '');
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate required fields.
    if (
        $email === '' ||
        $firstName === '' ||
        $lastName === '' ||
        $streetAddress === '' ||
        $city === '' ||
        $state === '' ||
        $zipCode === '' ||
        $phoneNumber === '' ||
        $password === ''
    ) {

        $error_message =
            'Please complete all required fields.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error_message =
            'Please enter a valid email address.';

    } elseif (strlen($email) > 100) {

        $error_message =
            'Email address cannot exceed 100 characters.';

    } elseif (
        strlen($firstName) > 50 ||
        strlen($lastName) > 50
    ) {

        $error_message =
            'First and last names cannot exceed 50 characters.';

    } elseif (strlen($streetAddress) > 100) {

        $error_message =
            'Street address cannot exceed 100 characters.';

    } elseif (strlen($city) > 50) {

        $error_message =
            'City cannot exceed 50 characters.';

    } elseif (
        strlen($state) !== 2 ||
        !ctype_alpha($state)
    ) {

        $error_message =
            'Please enter a valid two-letter state abbreviation.';

    } elseif (
        strlen($zipCode) > 10 ||
        !preg_match('/^\d{5}(-\d{4})?$/', $zipCode)
    ) {

        $error_message =
            'Please enter a valid ZIP Code.';

    } elseif (
        strlen($phoneNumber) > 20 ||
        !preg_match(
            '/^[0-9\-\(\)\s\.]+$/',
            $phoneNumber
        )
    ) {

        $error_message =
            'Please enter a valid phone number.';

    } elseif (strlen($password) < 8) {

        $error_message =
            'Password must be at least 8 characters long.';

    } elseif (!preg_match('/[A-Z]/', $password)) {

        $error_message =
            'Password must contain at least one uppercase letter.';

    } elseif (!preg_match('/[a-z]/', $password)) {

        $error_message =
            'Password must contain at least one lowercase letter.';

    } elseif (!preg_match('/[0-9]/', $password)) {

        $error_message =
            'Password must contain at least one number.';

    } elseif (strlen($password) > 255) {

        $error_message =
            'Password cannot exceed 255 characters.';

    } elseif (UserDB::getUserByEmail($email) !== null) {

        $error_message =
            'An account with this email address already exists.';

    } else {

        // Create the customer record.
        $customer = new Customer(
            null,
            $email,
            $firstName,
            $lastName,
            $streetAddress,
            $city,
            $state,
            $zipCode,
            $phoneNumber,
            $password
        );

        $customerController = new CustomerController();

        if ($customerController->createCustomer($customer)) {

            // UserLevel 2 represents a customer.
            $user = new User(
                $email,
                $password,
                2
            );

            if (UserDB::createUser($user)) {

                $success_message =
                    'Registration successful. You may now log in.';

            } else {

                $error_message =
                    'The customer was created, but the login account could not be created.';
            }

        } else {

            $error_message =
                'Customer registration was unsuccessful.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        Customer Registration - Customer Complaint Management System
    </title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="container">

        <h1>Customer Complaint Management System</h1>

        <h2>Customer Registration</h2>

        <?php if ($error_message !== ''): ?>

            <p>
                <?php echo htmlspecialchars($error_message); ?>
            </p>

        <?php endif; ?>

        <?php if ($success_message !== ''): ?>

            <p>
                <?php echo htmlspecialchars($success_message); ?>
            </p>

            <p>
                <a href="login.php">
                    Customer Login
                </a>
            </p>

        <?php endif; ?>

        <form method="post" action="register.php">

            <p>
                <label for="email">
                    Email:
                </label><br>

                <input
                    type="email"
                    id="email"
                    name="email"
                    maxlength="100"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST['email'] ?? ''
                    );
                    ?>"
                    required
                >
            </p>

            <p>
                <label for="first_name">
                    First Name:
                </label><br>

                <input
                    type="text"
                    id="first_name"
                    name="first_name"
                    maxlength="50"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST['first_name'] ?? ''
                    );
                    ?>"
                    required
                >
            </p>

            <p>
                <label for="last_name">
                    Last Name:
                </label><br>

                <input
                    type="text"
                    id="last_name"
                    name="last_name"
                    maxlength="50"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST['last_name'] ?? ''
                    );
                    ?>"
                    required
                >
            </p>

            <p>
                <label for="street_address">
                    Street Address:
                </label><br>

                <input
                    type="text"
                    id="street_address"
                    name="street_address"
                    maxlength="100"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST['street_address'] ?? ''
                    );
                    ?>"
                    required
                >
            </p>

            <p>
                <label for="city">
                    City:
                </label><br>

                <input
                    type="text"
                    id="city"
                    name="city"
                    maxlength="50"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST['city'] ?? ''
                    );
                    ?>"
                    required
                >
            </p>

            <p>
                <label for="state">
                    State:
                </label><br>

                <input
                    type="text"
                    id="state"
                    name="state"
                    maxlength="2"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST['state'] ?? ''
                    );
                    ?>"
                    required
                >
            </p>

            <p>
                <label for="zip_code">
                    ZIP Code:
                </label><br>

                <input
                    type="text"
                    id="zip_code"
                    name="zip_code"
                    maxlength="10"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST['zip_code'] ?? ''
                    );
                    ?>"
                    required
                >
            </p>

            <p>
                <label for="phone_number">
                    Phone Number:
                </label><br>

                <input
                    type="text"
                    id="phone_number"
                    name="phone_number"
                    maxlength="20"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST['phone_number'] ?? ''
                    );
                    ?>"
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
                    minlength="8"
                    maxlength="255"
                    required
                >
            </p>

            <p>
                Password must be at least 8 characters and contain
                an uppercase letter, lowercase letter, and number.
            </p>

            <p>
                <button type="submit">
                    Register
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