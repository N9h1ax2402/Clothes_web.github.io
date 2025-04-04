<?php
require_once __DIR__ . "/../../config/database.php";

class Product {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function createProduct($name, $price, $image) {
        $sql = "INSERT INTO product (name, price, image) VALUES (:name, :price, :image)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':price', $price, PDO::PARAM_STR);
        $stmt->bindValue(':image', $image, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function getAllProducts($sort_by = "name_asc") {
        $order_by = match ($sort_by) {
            'name_asc' => "ORDER BY name ASC",
            'name_desc' => "ORDER BY name DESC",
            'price_asc' => "ORDER BY CAST(REPLACE(REPLACE(price, '.', ''), ' VNĐ', '') AS DECIMAL) ASC",
            'price_desc' => "ORDER BY CAST(REPLACE(REPLACE(price, '.', ''), ' VNĐ', '') AS DECIMAL) DESC",
            default => "ORDER BY name ASC",
        };

        $sql = "SELECT * FROM product $order_by";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductById($id) {
        $sql = "SELECT * FROM product WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProduct($id, $name, $price, $image) {
        $sql = "UPDATE product SET name = :name, price = :price, image = :image WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':price', $price, PDO::PARAM_STR);
        $stmt->bindValue(':image', $image, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function deleteProduct($id) {
        $sql = "DELETE FROM product WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function searchByName($search) {
        if (empty($search)) {
            return [];
        }
        
        $sql = "SELECT id, name FROM product 
                WHERE name LIKE ? 
                ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, "%$search%", PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
}
?>
