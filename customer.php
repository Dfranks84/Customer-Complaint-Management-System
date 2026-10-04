<?php

session_start();

require_once('util/security.php');
require_once('controller/CustomerController.php');
require_once('controller/ComplaintController.php');

// Only customers are authorized to access this page.
Security::checkAuthority('customer');

$customerController = new CustomerController();
$complaintController = new ComplaintController();

// Find the logged-in customer using the e-mail address
// stored in the session.
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

// Get only complaints belonging to the logged-in customer.
$customerComplaints = array();

if ($currentCustomer !== null) {

    $allComplaints = $complaintController->getAllComplaints();

    foreach ($allComplaints as $complaint) {

        if (
            $complaint['customer_id'] ==
            $currentCustomer['customer_id']
        ) {
            $customerComplaints[] = $complaint;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Customer - Customer Complaint Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="container">

        <h1>Customer Complaint Management System</h1>

        <h2>Customer Page</h2>

        <p>
            Welcome,
            <?php echo htmlspecialchars($_SESSION['email']); ?>.
        </p>

        <?php if ($currentCustomer !== null): ?>

            <p>
                Customer:
                <?php
                echo htmlspecialchars(
                    $currentCustomer['first_name'] . ' ' .
                    $currentCustomer['last_name']
                );
                ?>
            </p>

            <h3>Customer Options</h3>

            <ul>
                <li>
                    <a href="submit_complaint.php">
                        Submit Complaint
                    </a>
                </li>

                <li>
                    <a href="update_customer.php">
                        Update Customer Information
                    </a>
                </li>
            </ul>

            <h3>My Complaints</h3>

            <?php if (count($customerComplaints) > 0): ?>

                <table border="1" cellpadding="8">

                    <tr>
                        <th>Complaint ID</th>
                        <th>Description</th>
                        <th>Technician Notes</th>
                        <th>Status</th>
                        <th>Date Created</th>
                    </tr>

                    <?php foreach ($customerComplaints as $complaint): ?>

                        <tr>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $complaint['complaint_id']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $complaint['complaint_description']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $complaint['technician_notes'] ??
                                    'No technician notes'
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $complaint['status']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $complaint['date_created']
                                );
                                ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </table>

            <?php else: ?>

                <p>No complaints have been submitted.</p>

            <?php endif; ?>

        <?php else: ?>

            <p>
                Customer information could not be found.
            </p>

        <?php endif; ?>

        <p>
            <a href="logout.php">Logout</a>
        </p>

    </div>

</body>

</html>