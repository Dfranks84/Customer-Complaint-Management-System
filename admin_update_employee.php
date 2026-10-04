<?php

session_start();

require_once('util/security.php');
require_once('model/Technician.php');
require_once('controller/TechnicianController.php');

// Only administrators are authorized to access this page.
Security::checkAuthority('admin');

$technicianController = new TechnicianController();

$error_message = '';
$success_message = '';

// Get the employee ID.
$employeeID = (int)($_GET['id'] ?? $_POST['employee_id'] ?? 0);

// Get the employee information.
$employee = null;

if ($employeeID > 0) {
    $employee =
        $technicianController->getEmployeeByID($employeeID);
}

// Process the update form.
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    $employee !== null
) {

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phoneExtension = trim($_POST['phone_extension'] ?? '');
    $level = $_POST['level'] ?? '';

    // User ID is add-only and cannot be changed.
    $userID = $employee['user_id'];

    // Keep the employee's existing password.
    $password = $employee['password'];

    if (
        $firstName === '' ||
        $lastName === '' ||
        $email === '' ||
        $level === ''
    ) {

        $error_message =
            'Please complete all required employee fields.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error_message =
            'Please enter a valid email address.';

    } elseif (
        strlen($firstName) > 50 ||
        strlen($lastName) > 50
    ) {

        $error_message =
            'First and last names cannot exceed 50 characters.';

    } elseif (strlen($email) > 100) {

        $error_message =
            'Email address cannot exceed 100 characters.';

    } elseif (strlen($phoneExtension) > 10) {

        $error_message =
            'Phone extension cannot exceed 10 characters.';

    } elseif (
        $level !== 'Administrator' &&
        $level !== 'Technician'
    ) {

        $error_message =
            'Please select a valid employee level.';

    } else {

        $updatedEmployee = new Technician(
            $employeeID,
            $userID,
            $firstName,
            $lastName,
            $email,
            $phoneExtension,
            $level,
            $password
        );

        if (
            $technicianController->updateTechnician(
                $updatedEmployee
            )
        ) {

            $success_message =
                'Employee information updated successfully.';

            // Reload the employee information.
            $employee =
                $technicianController->getEmployeeByID(
                    $employeeID
                );

        } else {

            $error_message =
                'Employee information could not be updated.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        Update Employee - Customer Complaint Management System
    </title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="container">

        <h1>Customer Complaint Management System</h1>

        <h2>Administrator - Update Employee</h2>

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

        <?php if ($employee !== null): ?>

            <form
                method="post"
                action="admin_update_employee.php"
            >

                <input
                    type="hidden"
                    name="employee_id"
                    value="<?php
                    echo htmlspecialchars(
                        $employee['employee_id']
                    );
                    ?>"
                >

                <p>
                    <label>
                        User ID:
                    </label><br>

                    <input
                        type="text"
                        value="<?php
                        echo htmlspecialchars(
                            $employee['user_id']
                        );
                        ?>"
                        readonly
                    >
                </p>

                <p>
                    <small>
                        User ID is add-only and cannot be changed.
                    </small>
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
                            $employee['first_name']
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
                            $employee['last_name']
                        );
                        ?>"
                        required
                    >
                </p>

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
                            $employee['email']
                        );
                        ?>"
                        required
                    >
                </p>

                <p>
                    <label for="phone_extension">
                        Phone Extension:
                    </label><br>

                    <input
                        type="text"
                        id="phone_extension"
                        name="phone_extension"
                        maxlength="10"
                        value="<?php
                        echo htmlspecialchars(
                            $employee['phone_extension'] ?? ''
                        );
                        ?>"
                    >
                </p>

                <p>
                    <label for="level">
                        Level:
                    </label><br>

                    <select
                        id="level"
                        name="level"
                        required
                    >

                        <option
                            value="Technician"
                            <?php
                            if (
                                $employee['level'] ===
                                'Technician'
                            ) {
                                echo 'selected';
                            }
                            ?>
                        >
                            Technician
                        </option>

                        <option
                            value="Administrator"
                            <?php
                            if (
                                $employee['level'] ===
                                'Administrator'
                            ) {
                                echo 'selected';
                            }
                            ?>
                        >
                            Administrator
                        </option>

                    </select>
                </p>

                <p>
                    <button type="submit">
                        Update Employee
                    </button>
                </p>

            </form>

        <?php else: ?>

            <p>
                Employee information could not be found.
            </p>

        <?php endif; ?>

        <p>
            <a href="manage_employees.php">
                Return to Manage Employees
            </a>
        </p>

    </div>

</body>

</html>