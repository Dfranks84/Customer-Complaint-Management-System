<?php

session_start();

require_once('util/security.php');
require_once('model/Complaint.php');
require_once('controller/CustomerController.php');
require_once('controller/ComplaintController.php');
require_once('model/Database.php');

// Only customers are authorized to access this page.
Security::checkAuthority('customer');

$conn = Database::connect();
$customerController = new CustomerController();
$complaintController = new ComplaintController();

$error_message = '';
$success_message = '';

// Find the logged-in customer.
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

// Get products/services for the drop-down list.
$productsServices = array();

$result = $conn->query(
    "SELECT product_service_id, name
     FROM products_services
     ORDER BY name"
);

if ($result) {
    $productsServices = $result->fetch_all(MYSQLI_ASSOC);
}

// Get complaint types for the drop-down list.
$complaintTypes = array();

$result = $conn->query(
    "SELECT complaint_type_id, type_name
     FROM complaint_types
     ORDER BY type_name"
);

if ($result) {
    $complaintTypes = $result->fetch_all(MYSQLI_ASSOC);
}

// Process the complaint form.
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    $currentCustomer !== null
) {

    $productServiceID =
        (int)($_POST['product_service_id'] ?? 0);

    $complaintTypeID =
        (int)($_POST['complaint_type_id'] ?? 0);

    $complaintDescription =
        trim($_POST['complaint_description'] ?? '');

    $imagePath = null;

    // Validate required complaint information.
    if (
        $productServiceID <= 0 ||
        $complaintTypeID <= 0 ||
        $complaintDescription === ''
    ) {

        $error_message =
            'Please complete all required complaint fields.';

    } elseif (strlen($complaintDescription) > 5000) {

        $error_message =
            'The complaint description is too long.';

    } else {

        // Process the optional image upload.
        if (
            isset($_FILES['complaint_image']) &&
            $_FILES['complaint_image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            if (
                $_FILES['complaint_image']['error'] !== UPLOAD_ERR_OK
            ) {

                $error_message =
                    'The image could not be uploaded.';

            } else {

                $allowedTypes = array(
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif'
                );

                $fileInfo = finfo_open(FILEINFO_MIME_TYPE);

                $mimeType = finfo_file(
                    $fileInfo,
                    $_FILES['complaint_image']['tmp_name']
                );

                finfo_close($fileInfo);

                if (!isset($allowedTypes[$mimeType])) {

                    $error_message =
                        'Only JPG, PNG, and GIF images are allowed.';

                } elseif (
                    $_FILES['complaint_image']['size'] > 5000000
                ) {

                    $error_message =
                        'The image must be 5 MB or smaller.';

                } else {

                    $uploadDirectory =
                        __DIR__ . '/images/uploads/';

                    if (!is_dir($uploadDirectory)) {
                        mkdir($uploadDirectory, 0755, true);
                    }

                    $extension = $allowedTypes[$mimeType];

                    $fileName =
                        'complaint_' .
                        time() . '_' .
                        bin2hex(random_bytes(4)) .
                        '.' . $extension;

                    $destination =
                        $uploadDirectory . $fileName;

                    if (
                        move_uploaded_file(
                            $_FILES['complaint_image']['tmp_name'],
                            $destination
                        )
                    ) {

                        $imagePath =
                            'images/uploads/' . $fileName;

                    } else {

                        $error_message =
                            'The image could not be saved.';
                    }
                }
            }
        }

        // Create the complaint if validation succeeded.
        if ($error_message === '') {

            $complaint = new Complaint(
                null,
                $currentCustomer['customer_id'],
                $productServiceID,
                $complaintTypeID,
                null,
                $complaintDescription,
                $imagePath,
                null,
                'Open',
                null,
                null,
                null
            );

            if (
                $complaintController->createComplaint($complaint)
            ) {

                $success_message =
                    'Complaint submitted successfully.';

            } else {

                $error_message =
                    'The complaint could not be submitted.';
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        Submit Complaint - Customer Complaint Management System
    </title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="container">

        <h1>Customer Complaint Management System</h1>

        <h2>Submit Complaint</h2>

        <?php if ($error_message !== ''): ?>

            <p>
                <?php echo htmlspecialchars($error_message); ?>
            </p>

        <?php endif; ?>

        <?php if ($success_message !== ''): ?>

            <p>
                <?php echo htmlspecialchars($success_message); ?>
            </p>

            <p>
                <a href="customer.php">
                    View My Complaints
                </a>
            </p>

        <?php endif; ?>

        <?php if ($currentCustomer !== null): ?>

            <form
                method="post"
                action="submit_complaint.php"
                enctype="multipart/form-data"
            >

                <p>
                    <label for="product_service_id">
                        Product/Service:
                    </label>
                    <br>

                    <select
                        id="product_service_id"
                        name="product_service_id"
                        required
                    >

                        <option value="">
                            Select a Product/Service
                        </option>

                        <?php
                        foreach ($productsServices as $product):
                        ?>

                            <option
                                value="<?php
                                echo htmlspecialchars(
                                    $product['product_service_id']
                                );
                                ?>"
                            >
                                <?php
                                echo htmlspecialchars(
                                    $product['name']
                                );
                                ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </p>

                <p>
                    <label for="complaint_type_id">
                        Complaint Type:
                    </label>
                    <br>

                    <select
                        id="complaint_type_id"
                        name="complaint_type_id"
                        required
                    >

                        <option value="">
                            Select a Complaint Type
                        </option>

                        <?php
                        foreach ($complaintTypes as $type):
                        ?>

                            <option
                                value="<?php
                                echo htmlspecialchars(
                                    $type['complaint_type_id']
                                );
                                ?>"
                            >
                                <?php
                                echo htmlspecialchars(
                                    $type['type_name']
                                );
                                ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </p>

                <p>
                    <label for="complaint_description">
                        Complaint Description:
                    </label>
                    <br>

                    <textarea
                        id="complaint_description"
                        name="complaint_description"
                        rows="6"
                        cols="50"
                        required
                    ><?php
                    echo htmlspecialchars(
                        $_POST['complaint_description'] ?? ''
                    );
                    ?></textarea>
                </p>

                <p>
                    <label for="complaint_image">
                        Upload Image:
                    </label>
                    <br>

                    <input
                        type="file"
                        id="complaint_image"
                        name="complaint_image"
                        accept=".jpg,.jpeg,.png,.gif"
                    >
                </p>

                <p>
                    Allowed image types:
                    JPG, PNG, and GIF.
                    Maximum size: 5 MB.
                </p>

                <p>
                    <button type="submit">
                        Submit Complaint
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