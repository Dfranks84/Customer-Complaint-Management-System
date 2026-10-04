<?php

session_start();

require_once('util/security.php');
require_once('model/Customer.php');
require_once('controller/CustomerController.php');

// Only customers are authorized to access this page.
Security::checkAuthority('customer');

$customerController = new CustomerController();

$error_message = '';
$success_message = '';

// Find the logged-in customer using the e-mail
// address stored in the session.
$customers = $customerController->getAllCustomers();
$currentCustomer = null;

foreach ($customers as $customer) {
    if (
        strtolower($customer['email']) ===
        strtolower($_SESSION['email'])
    ) {
        $currentCustomer = $customer;
        break;
    }
}

// Process the update form.
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    $currentCustomer !== null
) {

    $email = trim($_POST['email'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $streetAddress = trim($_POST['street_address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = strtoupper(trim($_POST['state'] ?? ''));
    $zipCode = trim($_POST['zip_code'] ?? '');
    $phoneNumber = trim($_POST['phone_number'] ?? '');

    // Keep the customer's existing password.
    $password = $currentCustomer['password'];

    // Validate required fields.
    if (
        $email === '' ||
        $firstName === '' ||
        $lastName === '' ||
        $streetAddress === '' ||
        $city === '' ||
        $state === '' ||
        $zipCode === '' ||
        $phoneNumber === ''
    ) {

        $error_message =
            'Please complete all required fields.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error_message =
            'Please enter a valid email address.';

    } elseif (strlen($state) !== 2) {

        $error_message =
            'Please enter a valid two-letter state abbreviation.';

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

    } elseif (strlen($zipCode) > 10) {

        $error_message =
            'ZIP Code cannot exceed 10 characters.';

    } elseif (strlen($phoneNumber) > 20) {

        $error_message =
            'Phone number cannot exceed 20 characters.';

    } else {

        $updatedCustomer = new Customer(
            $currentCustomer['customer_id'],
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

        if (
            $customerController->updateCustomer(
                $updatedCustomer
            )
        ) {

            // Update the current page data.
            $currentCustomer['email'] = $email;
            $currentCustomer['first_name'] = $firstName;
            $currentCustomer['last_name'] = $lastName;
            $currentCustomer['street_address'] =
                $streetAddress;
            $currentCustomer['city'] = $city;
            $currentCustomer['state'] = $state;
            $currentCustomer['zip_code'] = $zipCode;
            $currentCustomer['phone_number'] =
                $phoneNumber;

            $success_message =
                'Customer information updated successfully.';

        } else {

            $error_message =
                'Customer information could not be updated.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        Update Customer - Customer Complaint Management System
    </title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="container">

        <h1>Customer Complaint Management System</h1>

        <h2>Update Customer Information</h2>

        <?php if ($error_message !== ''): ?>

            <p>
                <?php echo htmlspecialchars($error_message); ?>
            </p>

        <?php endif; ?>

        <?php if ($success_message !== ''): ?>

            <p>
                <?php echo htmlspecialchars($success_message); ?>
            </p>

        <?php endif; ?>

        <?php if ($currentCustomer !== null): ?>

            <form
                method="post"
                action="update_customer.php"
            >

                <p>
                    <label for="email">Email:</label><br>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        maxlength="100"
                        value="<?php
                        echo htmlspecialchars(
                            $currentCustomer['email']
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
                            $currentCustomer['first_name']
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
                            $currentCustomer['last_name']
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
                            $currentCustomer['street_address']
                        );
                        ?>"
                        required
                    >
                </p>

                <p>
                    <label for="city">City:</label><br>

                    <input
                        type="text"
                        id="city"
                        name="city"
                        maxlength="50"
                        value="<?php
                        echo htmlspecialchars(
                            $currentCustomer['city']
                        );
                        ?>"
                        required
                    >
                </p>

                <p>
                    <label for="state">State:</label><br>

                    <input
                        type="text"
                        id="state"
                        name="state"
                        maxlength="2"
                        value="<?php
                        echo htmlspecialchars(
                            $currentCustomer['state']
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
                            $currentCustomer['zip_code']
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
                            $currentCustomer['phone_number']
                        );
                        ?>"
                        required
                    >
                </p>

                <p>
                    <button type="submit">
                        Update Information
                    </button>
                </p>

            </form>

        <?php else: ?>

            <p>
                Customer information could not be found.
            </p>

        <?php endif; ?>

        <p>
            <a href="customer.php">
                Return to Customer Page
            </a>
        </p>

    </div>

</body>

</html>