<?php

session_start();

require_once('util/security.php');
require_once('controller/CustomerController.php');

// Only administrators are authorized to access this page.
Security::checkAuthority('admin');

$customerController = new CustomerController();

// Get all registered customers.
$customers = $customerController->getAllCustomers();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        View Customers - Customer Complaint Management System
    </title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="container">

        <h1>Customer Complaint Management System</h1>

        <h2>View Customers</h2>

        <?php if (count($customers) > 0): ?>

            <table border="1" cellpadding="8">

                <tr>
                    <th>Customer ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Street Address</th>
                    <th>City</th>
                    <th>State</th>
                    <th>ZIP Code</th>
                    <th>Phone Number</th>
                    <th>Action</th>
                </tr>

                <?php foreach ($customers as $customer): ?>

                    <tr>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $customer['customer_id']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $customer['first_name'] .
                                ' ' .
                                $customer['last_name']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $customer['email']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $customer['street_address']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $customer['city']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $customer['state']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $customer['zip_code']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $customer['phone_number']
                            );
                            ?>
                        </td>

                        <td>
                            <a href="admin_update_customer.php?id=<?php
                            echo urlencode(
                                $customer['customer_id']
                            );
                            ?>">
                                Update
                            </a>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </table>

        <?php else: ?>

            <p>No customers were found.</p>

        <?php endif; ?>

        <p>
            <a href="admin.php">
                Return to Administrator Page
            </a>
        </p>

    </div>

</body>

</html>