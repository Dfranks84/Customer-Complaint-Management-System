<?php

session_start();

require_once('util/security.php');
require_once('model/Product.php');
require_once('controller/ProductController.php');

// Only administrators are authorized to access this page.
Security::checkAuthority('admin');

$productController = new ProductController();

$error_message = '';
$success_message = '';

// Process form actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    // Add a new product or service.
    if ($action === 'add') {

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '' || $description === '') {

            $error_message =
                'Please complete all required fields.';

        } else {

            $product = new Product(
                null,
                $name,
                $description
            );

            if ($productController->createProduct($product)) {

                $success_message =
                    'Product or service added successfully.';

            } else {

                $error_message =
                    'Product or service could not be added.';
            }
        }
    }

    // Update an existing product or service.
    if ($action === 'update') {

        $productServiceID =
            (int)($_POST['product_service_id'] ?? 0);

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (
            $productServiceID <= 0 ||
            $name === '' ||
            $description === ''
        ) {

            $error_message =
                'Please complete all required fields.';

        } else {

            $product = new Product(
                $productServiceID,
                $name,
                $description
            );

            if ($productController->updateProduct($product)) {

                $success_message =
                    'Product or service updated successfully.';

            } else {

                $error_message =
                    'Product or service could not be updated.';
            }
        }
    }

    // Delete a product or service.
    if ($action === 'delete') {

        $productServiceID =
            (int)($_POST['product_service_id'] ?? 0);

        if ($productServiceID > 0) {

            if (
                $productController->deleteProduct(
                    $productServiceID
                )
            ) {

                $success_message =
                    'Product or service deleted successfully.';

            } else {

                $error_message =
                    'Product or service could not be deleted.';
            }
        }
    }
}

// Get all products and services.
$products = $productController->getAllProducts();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        Manage Products and Services -
        Customer Complaint Management System
    </title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="container">

        <h1>Customer Complaint Management System</h1>

        <h2>Manage Products and Services</h2>

        <?php if ($error_message !== ''): ?>

            <p>
                <?php
                echo htmlspecialchars($error_message);
                ?>
            </p>

        <?php endif; ?>

        <?php if ($success_message !== ''): ?>

            <p>
                <?php
                echo htmlspecialchars($success_message);
                ?>
            </p>

        <?php endif; ?>

        <h3>Add Product or Service</h3>

        <form method="post" action="manage_products.php">

            <input
                type="hidden"
                name="action"
                value="add"
            >

            <p>
                <label for="name">
                    Name:
                </label><br>

                <input
                    type="text"
                    id="name"
                    name="name"
                    required
                >
            </p>

            <p>
                <label for="description">
                    Description:
                </label><br>

                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    cols="50"
                    required
                ></textarea>
            </p>

            <p>
                <button type="submit">
                    Add Product or Service
                </button>
            </p>

        </form>

        <h3>Existing Products and Services</h3>

        <?php if (count($products) > 0): ?>

            <?php foreach ($products as $product): ?>

                <form
                    method="post"
                    action="manage_products.php"
                >

                    <input
                        type="hidden"
                        name="action"
                        value="update"
                    >

                    <input
                        type="hidden"
                        name="product_service_id"
                        value="<?php
                        echo htmlspecialchars(
                            $product['product_service_id']
                        );
                        ?>"
                    >

                    <p>
                        <strong>
                            Product/Service ID:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $product['product_service_id']
                        );
                        ?>
                    </p>

                    <p>
                        <label>
                            Name:
                        </label><br>

                        <input
                            type="text"
                            name="name"
                            value="<?php
                            echo htmlspecialchars(
                                $product['name']
                            );
                            ?>"
                            required
                        >
                    </p>

                    <p>
                        <label>
                            Description:
                        </label><br>

                        <textarea
                            name="description"
                            rows="4"
                            cols="50"
                            required
                        ><?php
                        echo htmlspecialchars(
                            $product['description']
                        );
                        ?></textarea>
                    </p>

                    <p>
                        <button type="submit">
                            Update
                        </button>
                    </p>

                </form>

                <form
                    method="post"
                    action="manage_products.php"
                >

                    <input
                        type="hidden"
                        name="action"
                        value="delete"
                    >

                    <input
                        type="hidden"
                        name="product_service_id"
                        value="<?php
                        echo htmlspecialchars(
                            $product['product_service_id']
                        );
                        ?>"
                    >

                    <p>
                        <button type="submit">
                            Delete
                        </button>
                    </p>

                </form>

                <hr>

            <?php endforeach; ?>

        <?php else: ?>

            <p>
                No products or services were found.
            </p>

        <?php endif; ?>

        <p>
            <a href="admin.php">
                Return to Administrator Page
            </a>
        </p>

    </div>

</body>

</html>