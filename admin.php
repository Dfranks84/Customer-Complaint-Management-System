<?php

session_start();

require_once('util/security.php');

// Only administrators are authorized to access this page.
Security::checkAuthority('admin');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Administrator - Customer Complaint Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="container">

        <h1>Customer Complaint Management System</h1>

        <h2>Administrator Page</h2>

        <p>
            Welcome,
            <?php echo htmlspecialchars($_SESSION['email']); ?>.
        </p>

        <p>You are logged in as an Administrator.</p>

        <h3>Administrator Options</h3>

        <ul>
            <li>
                <a href="manage_customers.php">
                    View Customers
                </a>
            </li>

            <li>
                <a href="manage_complaints.php">
                    View Complaints
                </a>
            </li>

            <li>
                <a href="manage_employees.php">
                    Manage Employees
                </a>
            </li>

            <li>
                <a href="manage_products.php">
                    Manage Products and Services
                </a>
            </li>
        </ul>

        <p>
            <a href="logout.php">Logout</a>
        </p>

    </div>

</body>

</html>