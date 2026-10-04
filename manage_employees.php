<?php

session_start();

require_once('util/security.php');
require_once('model/Technician.php');
require_once('model/User.php');
require_once('model/UserDB.php');
require_once('controller/TechnicianController.php');

// Only administrators are authorized to access this page.
Security::checkAuthority('admin');

$technicianController = new TechnicianController();

$error_message = '';
$success_message = '';

// Process the Add Employee form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userID = trim($_POST['user_id'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phoneExtension = trim($_POST['phone_extension'] ?? '');
    $password = $_POST['password'] ?? '';
    $level = $_POST['level'] ?? '';

    if (
        $userID === '' ||
        $firstName === '' ||
        $lastName === '' ||
        $email === '' ||
        $password === '' ||
        $level === ''
    ) {

        $error_message =
            'Please complete all required employee fields.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error_message =
            'Please enter a valid email address.';

    } elseif (strlen($userID) > 50) {

        $error_message =
            'User ID cannot exceed 50 characters.';

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

    } elseif (strlen($password) < 8) {

        $error_message =
            'Password must be at least 8 characters long.';

    } elseif (
        $level !== 'Administrator' &&
        $level !== 'Technician'
    ) {

        $error_message =
            'Please select a valid employee level.';

    } elseif (UserDB::getUserByEmail($email) !== null) {

        $error_message =
            'A login account with this email address already exists.';

    } else {

        // UserLevel 1 = Administrator.
        // UserLevel 3 = Technician.
        if ($level === 'Administrator') {
            $userLevel = 1;
        } else {
            $userLevel = 3;
        }

        $user = new User(
            $email,
            $password,
            $userLevel
        );

        if (UserDB::createUser($user)) {

            // Technician passwords stored in the employees
            // table must be hashed for technician login.
            if ($level === 'Technician') {
                $employeePassword =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );
            } else {
                $employeePassword = $password;
            }

            $employee = new Technician(
                null,
                $userID,
                $firstName,
                $lastName,
                $email,
                $phoneExtension,
                $level,
                $employeePassword
            );

            if (
                $technicianController->createTechnician(
                    $employee
                )
            ) {

                $success_message =
                    'Employee added successfully.';

            } else {

                $error_message =
                    'The login was created, but the employee record could not be created.';
            }

        } else {

            $error_message =
                'The employee login account could not be created.';
        }
    }
}

// Get the current list of employees.
$employees = $technicianController->getAllEmployees();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        Manage Employees - Customer Complaint Management System
    </title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="container">

        <h1>Customer Complaint Management System</h1>

        <h2>Manage Employees</h2>

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

        <h3>Add Employee</h3>

        <form
            method="post"
            action="manage_employees.php"
        >

            <p>
                <label for="user_id">
                    User ID:
                </label><br>

                <input
                    type="text"
                    id="user_id"
                    name="user_id"
                    maxlength="50"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST['user_id'] ?? ''
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
                        $_POST['phone_extension'] ?? ''
                    );
                    ?>"
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
                    required
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
                    <option value="">
                        Select Employee Level
                    </option>

                    <option
                        value="Technician"
                        <?php
                        if (
                            ($_POST['level'] ?? '') ===
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
                            ($_POST['level'] ?? '') ===
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
                    Add Employee
                </button>
            </p>

        </form>

        <h3>Employees</h3>

        <?php if (count($employees) > 0): ?>

            <table border="1" cellpadding="8">

                <tr>
                    <th>Employee ID</th>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone Extension</th>
                    <th>Level</th>
                    <th>Action</th>
                </tr>

                <?php foreach ($employees as $employee): ?>

                    <tr>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $employee['employee_id']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $employee['user_id']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $employee['first_name'] .
                                ' ' .
                                $employee['last_name']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $employee['email']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $employee['phone_extension'] ??
                                ''
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $employee['level']
                            );
                            ?>
                        </td>

                        <td>
                            <a href="admin_update_employee.php?id=<?php
                            echo urlencode(
                                $employee['employee_id']
                            );
                            ?>">
                                Update
                            </a>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </table>

        <?php else: ?>

            <p>No employees have been added.</p>

        <?php endif; ?>

        <p>
            <a href="admin.php">
                Return to Administrator Page
            </a>
        </p>

    </div>

</body>

</html>