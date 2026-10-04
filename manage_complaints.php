<?php

session_start();

require_once('util/security.php');
require_once('model/Database.php');
require_once('controller/TechnicianController.php');

// Only administrators are authorized to access this page.
Security::checkAuthority('admin');

$conn = Database::connect();
$technicianController = new TechnicianController();

$error_message = '';
$success_message = '';

// Process complaint assignment.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $complaintID =
        (int)($_POST['complaint_id'] ?? 0);

    $technicianID =
        (int)($_POST['technician_id'] ?? 0);

    if ($complaintID <= 0 || $technicianID <= 0) {

        $error_message =
            'Please select a technician for the complaint.';

    } else {

        $sql = "UPDATE complaints
                SET technician_id = ?
                WHERE complaint_id = ?
                AND status = 'Open'";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ii",
            $technicianID,
            $complaintID
        );

        if ($stmt->execute()) {

            $success_message =
                'Complaint assigned successfully.';

        } else {

            $error_message =
                'The complaint could not be assigned.';
        }

        $stmt->close();
    }
}

// Get all technicians.
$technicians =
    $technicianController->getAllTechnicians();

// Get all open complaints.
$openComplaints = array();

$sql = "SELECT
            c.complaint_id,
            c.complaint_description,
            c.status,
            c.technician_id,
            cu.first_name,
            cu.last_name,
            e.first_name AS technician_first_name,
            e.last_name AS technician_last_name
        FROM complaints c
        INNER JOIN customers cu
            ON c.customer_id = cu.customer_id
        LEFT JOIN employees e
            ON c.technician_id = e.employee_id
        WHERE c.status = 'Open'
        ORDER BY c.date_created DESC";

$result = $conn->query($sql);

if ($result) {
    $openComplaints =
        $result->fetch_all(MYSQLI_ASSOC);
}

// Get open complaints with no technician assigned.
$unassignedComplaints = array();

foreach ($openComplaints as $complaint) {

    if ($complaint['technician_id'] === null) {
        $unassignedComplaints[] = $complaint;
    }
}

// Get technician open-complaint counts.
$technicianCounts = array();

$sql = "SELECT
            e.employee_id,
            e.first_name,
            e.last_name,
            COUNT(c.complaint_id) AS open_complaints
        FROM employees e
        LEFT JOIN complaints c
            ON e.employee_id = c.technician_id
            AND c.status = 'Open'
        WHERE e.level = 'Technician'
        GROUP BY
            e.employee_id,
            e.first_name,
            e.last_name
        ORDER BY
            e.last_name,
            e.first_name";

$result = $conn->query($sql);

if ($result) {
    $technicianCounts =
        $result->fetch_all(MYSQLI_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        Manage Complaints - Customer Complaint Management System
    </title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="container">

        <h1>Customer Complaint Management System</h1>

        <h2>Manage Complaints</h2>

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

        <h3>Open Complaints</h3>

        <?php if (count($openComplaints) > 0): ?>

            <table border="1" cellpadding="8">

                <tr>
                    <th>Complaint ID</th>
                    <th>Customer</th>
                    <th>Description</th>
                    <th>Assigned Technician</th>
                </tr>

                <?php foreach ($openComplaints as $complaint): ?>

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
                                $complaint['first_name'] .
                                ' ' .
                                $complaint['last_name']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $complaint[
                                    'complaint_description'
                                ]
                            );
                            ?>
                        </td>

                        <td>
                            <?php

                            if (
                                $complaint['technician_id']
                                !== null
                            ) {

                                echo htmlspecialchars(
                                    $complaint[
                                        'technician_first_name'
                                    ] .
                                    ' ' .
                                    $complaint[
                                        'technician_last_name'
                                    ]
                                );

                            } else {

                                echo 'Not Assigned';
                            }

                            ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </table>

        <?php else: ?>

            <p>No open complaints were found.</p>

        <?php endif; ?>

        <h3>Unassigned Open Complaints</h3>

        <?php if (count($unassignedComplaints) > 0): ?>

            <?php foreach ($unassignedComplaints as $complaint): ?>

                <form
                    method="post"
                    action="manage_complaints.php"
                >

                    <p>
                        <strong>
                            Complaint #
                            <?php
                            echo htmlspecialchars(
                                $complaint['complaint_id']
                            );
                            ?>
                        </strong>

                        -
                        <?php
                        echo htmlspecialchars(
                            $complaint[
                                'complaint_description'
                            ]
                        );
                        ?>
                    </p>

                    <input
                        type="hidden"
                        name="complaint_id"
                        value="<?php
                        echo htmlspecialchars(
                            $complaint['complaint_id']
                        );
                        ?>"
                    >

                    <p>
                        <label>
                            Assign Technician:
                        </label>

                        <select
                            name="technician_id"
                            required
                        >

                            <option value="">
                                Select Technician
                            </option>

                            <?php
                            foreach (
                                $technicians as $technician
                            ):
                            ?>

                                <option
                                    value="<?php
                                    echo htmlspecialchars(
                                        $technician[
                                            'employee_id'
                                        ]
                                    );
                                    ?>"
                                >
                                    <?php
                                    echo htmlspecialchars(
                                        $technician[
                                            'first_name'
                                        ] .
                                        ' ' .
                                        $technician[
                                            'last_name'
                                        ]
                                    );
                                    ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <button type="submit">
                            Assign Complaint
                        </button>
                    </p>

                </form>

            <?php endforeach; ?>

        <?php else: ?>

            <p>
                No unassigned open complaints were found.
            </p>

        <?php endif; ?>

        <h3>Technician Open Complaint Counts</h3>

        <?php if (count($technicianCounts) > 0): ?>

            <table border="1" cellpadding="8">

                <tr>
                    <th>Technician</th>
                    <th>Open Complaints</th>
                </tr>

                <?php foreach ($technicianCounts as $technician): ?>

                    <tr>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $technician['first_name'] .
                                ' ' .
                                $technician['last_name']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $technician[
                                    'open_complaints'
                                ]
                            );
                            ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </table>

        <?php else: ?>

            <p>No technicians were found.</p>

        <?php endif; ?>

        <p>
            <a href="admin.php">
                Return to Administrator Page
            </a>
        </p>

    </div>

</body>

</html>