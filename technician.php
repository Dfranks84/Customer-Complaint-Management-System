<?php

session_start();

require_once('util/security.php');
require_once('model/Database.php');

// Only technicians are authorized to access this page.
Security::checkAuthority('technician');

$conn = Database::connect();

$employeeID = (int)($_SESSION['employee_id'] ?? 0);

// Get the logged-in technician's information.
$technician = null;

$sql = "SELECT employee_id, user_id, first_name, last_name,
               email, phone_extension
        FROM employees
        WHERE employee_id = ?
        AND level = 'Technician'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employeeID);
$stmt->execute();

$result = $stmt->get_result();
$technician = $result->fetch_assoc();

$stmt->close();

// Get complaints assigned to this technician.
$complaints = array();

$sql = "SELECT
            c.complaint_id,
            c.complaint_description,
            c.image_path,
            c.technician_notes,
            c.status,
            c.date_created,
            c.resolution_date,
            c.resolution_notes,
            cu.first_name AS customer_first_name,
            cu.last_name AS customer_last_name,
            ps.name AS product_service,
            ct.type_name AS complaint_type
        FROM complaints c
        INNER JOIN customers cu
            ON c.customer_id = cu.customer_id
        INNER JOIN products_services ps
            ON c.product_service_id = ps.product_service_id
        INNER JOIN complaint_types ct
            ON c.complaint_type_id = ct.complaint_type_id
        WHERE c.technician_id = ?
        ORDER BY c.date_created DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employeeID);
$stmt->execute();

$result = $stmt->get_result();

$complaints = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        Technician - Customer Complaint Management System
    </title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="container">

        <h1>Customer Complaint Management System</h1>

        <h2>Technician Page</h2>

        <?php if ($technician): ?>

            <p>
                Welcome,
                <?php
                echo htmlspecialchars(
                    $technician['first_name'] . ' ' .
                    $technician['last_name']
                );
                ?>.
            </p>

            <p>
                User ID:
                <?php
                echo htmlspecialchars(
                    $technician['user_id']
                );
                ?>
            </p>

        <?php endif; ?>

        <h3>Technician Options</h3>

        <ul>
            <li>
                <a href="change_technician_password.php">
                    Change Password
                </a>
            </li>
        </ul>

        <h3>Assigned Complaints</h3>

        <?php if (count($complaints) > 0): ?>

            <?php foreach ($complaints as $complaint): ?>

                <div>

                    <h4>
                        Complaint #
                        <?php
                        echo htmlspecialchars(
                            $complaint['complaint_id']
                        );
                        ?>
                    </h4>

                    <p>
                        <strong>Customer:</strong>

                        <?php
                        echo htmlspecialchars(
                            $complaint['customer_first_name'] .
                            ' ' .
                            $complaint['customer_last_name']
                        );
                        ?>
                    </p>

                    <p>
                        <strong>Product/Service:</strong>

                        <?php
                        echo htmlspecialchars(
                            $complaint['product_service']
                        );
                        ?>
                    </p>

                    <p>
                        <strong>Complaint Type:</strong>

                        <?php
                        echo htmlspecialchars(
                            $complaint['complaint_type']
                        );
                        ?>
                    </p>

                    <p>
                        <strong>Description:</strong>

                        <?php
                        echo htmlspecialchars(
                            $complaint[
                                'complaint_description'
                            ]
                        );
                        ?>
                    </p>

                    <p>
                        <strong>Status:</strong>

                        <?php
                        echo htmlspecialchars(
                            $complaint['status']
                        );
                        ?>
                    </p>

                    <p>
                        <strong>Date Created:</strong>

                        <?php
                        echo htmlspecialchars(
                            $complaint['date_created']
                        );
                        ?>
                    </p>

                    <?php if (!empty($complaint['image_path'])): ?>

                        <p>
                            <strong>Complaint Image:</strong>
                        </p>

                        <p>
                            <img
                                src="<?php
                                echo htmlspecialchars(
                                    $complaint['image_path']
                                );
                                ?>"
                                alt="Complaint Image"
                                style="max-width: 400px;"
                            >
                        </p>

                    <?php endif; ?>

                    <p>
                        <strong>Technician Notes:</strong>

                        <?php
                        echo htmlspecialchars(
                            $complaint['technician_notes']
                            ?? 'No technician notes'
                        );
                        ?>
                    </p>

                    <?php
                    if (
                        $complaint['status'] === 'Closed'
                    ):
                    ?>

                        <p>
                            <strong>Resolution Date:</strong>

                            <?php
                            echo htmlspecialchars(
                                $complaint['resolution_date']
                                ?? ''
                            );
                            ?>
                        </p>

                        <p>
                            <strong>Resolution Notes:</strong>

                            <?php
                            echo htmlspecialchars(
                                $complaint['resolution_notes']
                                ?? ''
                            );
                            ?>
                        </p>

                    <?php endif; ?>

                    <?php
                    if (
                        $complaint['status'] === 'Open'
                    ):
                    ?>

                        <p>
                            <a href="update_complaint.php?id=<?php
                            echo urlencode(
                                $complaint['complaint_id']
                            );
                            ?>">
                                Update Complaint
                            </a>
                        </p>

                    <?php endif; ?>

                    <hr>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p>
                No complaints are currently assigned to you.
            </p>

        <?php endif; ?>

        <p>
            <a href="logout.php">Logout</a>
        </p>

    </div>

</body>

</html>