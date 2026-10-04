<?php

session_start();

// SDC342L Project - Main application page.

// Load the database connection.
require_once('config/database.php');

// Week 3 MVC Integration - Load controller classes.
require_once('controller/CustomerController.php');
require_once('controller/ComplaintController.php');
require_once('controller/TechnicianController.php');

// Week 4 Site Security - Load authentication controller.
require_once('controller/UserController.php');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Customer Complaint Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="container">

        <h1>Customer Complaint Management System</h1>

        <p>Welcome to the Customer Complaint Management System.</p>

        <h2>Customer</h2>

        <ul>
            <li><a href="register.php">Register Account</a></li>
            <li><a href="login.php">Customer Login</a></li>
            <li><a href="#">Submit Complaint</a></li>
        </ul>

        <h2>Employee</h2>

        <ul>
            <li><a href="login.php">Technician Login</a></li>
            <li><a href="login.php">Administrator Login</a></li>
        </ul>

    </div>

</body>

</html>