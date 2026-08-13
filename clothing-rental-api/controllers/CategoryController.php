<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../utils/Response.php';

class CategoryController {
    private $db;
    private $category;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->category = new Category($this->db);
    }

    public function getAll() {
        $stmt = $this->category->read();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $categories_arr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($categories_arr, $row);
            }
            Response::success("Categories retrieved successfully", $categories_arr, 200);
        } else {
            Response::success("No categories found", [], 200);
        }
    }

    public function getProductsByCategory($id) {
        $product = new Product($this->db);
        $stmt = $product->readByCategory($id);
        $num = $stmt->rowCount();

        if ($num > 0) {
            $products_arr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($products_arr, $row);
            }
            Response::success("Products retrieved successfully", $products_arr, 200);
        } else {
            Response::success("No products found for this category", [], 200);
        }
    }
}
?>
