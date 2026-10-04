<?php

session_start();

require_once('util/security.php');
require_once('model/Database.php');

// Only technicians are authorized to access this page.
Security::checkAuthority('technician');

$conn = Database::connect();

$employeeID = (int)($_SESSION['employee_id'] ?? 0);
$complaintID = (int)($_GET['id'] ?? $_POST['complaint_id'] ?? 0);

$error_message = '';
$success_message = '';

// Make sure a valid complaint ID was supplied.
if ($complaintID <= 0) {
    header('Location: technician.php');
    exit();
}

// Get the complaint and make sure it is assigned
// to the currently logged-in technician.
function getComplaint($conn, $complaintID, $employeeID)
{
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
            WHERE c.complaint_id = ?
            AND c.technician_id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ii",
        $complaintID,
        $employeeID
    );

    $stmt->execute();

    $result = $stmt->get_result();
    $complaint = $result->fetch_assoc();

    $stmt->close();

    return $complaint;
}

$complaint = getComplaint(
    $conn,
    $complaintID,
    $employeeID
);

// Prevent a technician from opening a complaint
// that is not assigned to them.
if (!$complaint) {
    header('Location: technician.php');
    exit();
}

// Process the update form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $technicianNotes =
        trim($_POST['technician_notes'] ?? '');

    $status =
        $_POST['status'] ?? 'Open';

    $resolutionDate =
        trim($_POST['resolution_date'] ?? '');

    $resolutionNotes =
        trim($_POST['resolution_notes'] ?? '');

    // Validate technician notes length.
    if (strlen($technicianNotes) > 5000) {

        $error_message =
            'Technician notes are too long.';

    } elseif (
        $status !== 'Open' &&
        $status !== 'Closed'
    ) {

        $error_message =
            'Invalid complaint status.';

    } elseif (
        $status === 'Closed' &&
        $resolutionDate === ''
    ) {

        $error_message =
            'A resolution date is required to resolve the complaint.';

    } elseif (
        $status === 'Closed' &&
        $resolutionNotes === ''
    ) {

        $error_message =
            'Resolution notes are required to resolve the complaint.';

    } elseif (
        strlen($resolutionNotes) > 5000
    ) {

        $error_message =
            'Resolution notes are too long.';

    } else {

        // Open complaints do not need resolution information.
        if ($status === 'Open') {
            $resolutionDate = null;
            $resolutionNotes = null;
        }

        $sql = "UPDATE complaints
                SET technician_notes = ?,
                    status = ?,
                    resolution_date = ?,
                    resolution_notes = ?
                WHERE complaint_id = ?
                AND technician_id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ssssii",
            $technicianNotes,
            $status,
            $resolutionDate,
            $resolutionNotes,
            $complaintID,
            $employeeID
        );

        if ($stmt->execute()) {

            $success_message =
                'Complaint updated successfully.';

        } else {

            $error_message =
                'The complaint could not be updated.';
        }

        $stmt->close();

        // Reload the complaint after the update.
        $complaint = getComplaint(
            $conn,
            $complaintID,
            $employeeID
        );
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        Update Complaint - Customer Complaint Management System
    </title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="container">

        <h1>Customer Complaint Management System</h1>

        <h2>
            Update Complaint #
            <?php
            echo htmlspecialchars(
                $complaint['complaint_id']
            );
            ?>
        </h2>

        <?php if ($error_message !== ''): ?>

            <p>
                <?php
                echo htmlspecialchars(
                    $error_message
                );
                ?>
            </p>

        <?php endif; ?>

        <?php if ($success_message !== ''): ?>

            <p>
                <?php
                echo htmlspecialchars(
                    $success_message
                );
                ?>
            </p>

        <?php endif; ?>

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
                $complaint['complaint_description']
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

        <form
            method="post"
            action="update_complaint.php"
        >

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
                <label for="technician_notes">
                    Technician Notes/Analysis:
                </label><br>

                <textarea
                    id="technician_notes"
                    name="technician_notes"
                    rows="6"
                    cols="60"
                ><?php
                echo htmlspecialchars(
                    $complaint['technician_notes']
                    ?? ''
                );
                ?></textarea>
            </p>

            <p>
                <label for="status">
                    Status:
                </label><br>

                <select
                    id="status"
                    name="status"
                    required
                >

                    <option
                        value="Open"
                        <?php
                        if (
                            $complaint['status'] === 'Open'
                        ) {
                            echo 'selected';
                        }
                        ?>
                    >
                        Open
                    </option>

                    <option
                        value="Closed"
                        <?php
                        if (
                            $complaint['status'] === 'Closed'
                        ) {
                            echo 'selected';
                        }
                        ?>
                    >
                        Closed
                    </option>

                </select>
            </p>

            <p>
                <label for="resolution_date">
                    Resolution Date:
                </label><br>

                <input
                    type="date"
                    id="resolution_date"
                    name="resolution_date"
                    value="<?php
                    echo htmlspecialchars(
                        $complaint['resolution_date']
                        ?? ''
                    );
                    ?>"
                >
            </p>

            <p>
                <label for="resolution_notes">
                    Resolution Notes:
                </label><br>

                <textarea
                    id="resolution_notes"
                    name="resolution_notes"
                    rows="6"
                    cols="60"
                ><?php
                echo htmlspecialchars(
                    $complaint['resolution_notes']
                    ?? ''
                );
                ?></textarea>
            </p>

            <p>
                <button type="submit">
                    Update Complaint
                </button>
            </p>

        </form>

        <p>
            <a href="technician.php">
                Return to Technician Page
            </a>
        </p>

    </div>

</body>

</html>