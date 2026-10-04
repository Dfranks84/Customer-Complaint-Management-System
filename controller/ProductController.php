<?php

require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/Product.php';

/**
 * Product Controller
 * Handles CRUD operations for products and services.
 */
class ProductController
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::connect();
    }

    // CREATE - Add a new product or service.
    public function createProduct(Product $product)
    {
        $sql = "INSERT INTO products_services
                (name, description)
                VALUES (?, ?)";

        $stmt = $this->conn->prepare($sql);

        $name = $product->getName();
        $description = $product->getDescription();

        $stmt->bind_param(
            "ss",
            $name,
            $description
        );

        return $stmt->execute();
    }

    // READ - Get one product or service by ID.
    public function getProductByID($productServiceID)
    {
        $sql = "SELECT *
                FROM products_services
                WHERE product_service_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $productServiceID);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    // READ - Get all products and services.
    public function getAllProducts()
    {
        $sql = "SELECT *
                FROM products_services
                ORDER BY name";

        $result = $this->conn->query($sql);

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // UPDATE - Update an existing product or service.
    public function updateProduct(Product $product)
    {
        $sql = "UPDATE products_services
                SET name = ?,
                    description = ?
                WHERE product_service_id = ?";

        $stmt = $this->conn->prepare($sql);

        $name = $product->getName();
        $description = $product->getDescription();
        $productServiceID =
            $product->getProductServiceID();

        $stmt->bind_param(
            "ssi",
            $name,
            $description,
            $productServiceID
        );

        return $stmt->execute();
    }

    // DELETE - Delete a product or service.
    public function deleteProduct($productServiceID)
    {
        $sql = "DELETE FROM products_services
                WHERE product_service_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $productServiceID);

        return $stmt->execute();
    }
}