<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ProductController {
    private $db;
    private $product;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->product = new Product($this->db);
    }

    public function getAll() {
        $stmt = $this->product->read();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $products_arr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($products_arr, $row);
            }
            Response::success("Products retrieved successfully", $products_arr, 200);
        } else {
            Response::success("No products found", [], 200);
        }
    }

    public function getOne($id) {
        $this->product->id = $id;
        $row = $this->product->readOne();

        if ($row != null) {
            Response::success("Product retrieved successfully", $row, 200);
        } else {
            Response::error("Product not found", [], 404);
        }
    }

    public function create($data) {
        AuthMiddleware::authenticate();

        $errors = Validator::validateRequired($data, ['category_id', 'name', 'price_per_day', 'size']);
        if (!empty($errors)) {
            Response::error("Validation failed", $errors, 422);
        }

        if (!Validator::validateNumeric($data['price_per_day'])) {
            Response::error("Validation failed", ['price_per_day' => 'Price must be numeric'], 422);
        }

        $category = new Category($this->db);
        $category->id = $data['category_id'];
        if (!$category->exists()) {
            Response::error("Validation failed", ['category_id' => 'Category does not exist'], 422);
        }

        $this->product->category_id = $data['category_id'];
        $this->product->name = $data['name'];
        $this->product->description = $data['description'] ?? '';
        $this->product->price_per_day = $data['price_per_day'];
        $this->product->size = $data['size'];
        $this->product->image = $data['image'] ?? '';
        $this->product->status = 'available';

        if ($this->product->create()) {
            Response::success("Product created successfully", ['id' => $this->product->id], 201);
        } else {
            Response::error("Unable to create product", [], 500);
        }
    }

    public function update($id, $data) {
        AuthMiddleware::authenticate();

        $this->product->id = $id;
        if (!$this->product->exists()) {
            Response::error("Product not found", [], 404);
        }

        $errors = Validator::validateRequired($data, ['category_id', 'name', 'price_per_day', 'size', 'status']);
        if (!empty($errors)) {
            Response::error("Validation failed", $errors, 422);
        }
        
        if (!Validator::validateNumeric($data['price_per_day'])) {
            Response::error("Validation failed", ['price_per_day' => 'Price must be numeric'], 422);
        }
        
        $allowedStatuses = ['available', 'rented'];
        if (!Validator::validateEnum($data['status'], $allowedStatuses)) {
            Response::error("Validation failed", ['status' => 'Invalid status'], 422);
        }
        
        $category = new Category($this->db);
        $category->id = $data['category_id'];
        if (!$category->exists()) {
            Response::error("Validation failed", ['category_id' => 'Category does not exist'], 422);
        }

        $this->product->category_id = $data['category_id'];
        $this->product->name = $data['name'];
        $this->product->description = $data['description'] ?? '';
        $this->product->price_per_day = $data['price_per_day'];
        $this->product->size = $data['size'];
        $this->product->image = $data['image'] ?? '';
        $this->product->status = $data['status'];

        if ($this->product->update()) {
            Response::success("Product updated successfully", [], 200);
        } else {
            Response::error("Unable to update product", [], 500);
        }
    }

    public function delete($id) {
        AuthMiddleware::authenticate();

        $this->product->id = $id;
        if (!$this->product->exists()) {
            Response::error("Product not found", [], 404);
        }

        if ($this->product->delete()) {
            Response::success("Product deleted successfully", [], 200);
        } else {
            Response::error("Unable to delete product", [], 500);
        }
    }

    public function search($query_params) {
        if (!isset($query_params['q']) || empty(trim($query_params['q']))) {
            Response::error("Search query 'q' is required", [], 400);
        }

        $keywords = $query_params['q'];
        $stmt = $this->product->search($keywords);
        $num = $stmt->rowCount();

        if ($num > 0) {
            $products_arr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($products_arr, $row);
            }
            Response::success("Products found", $products_arr, 200);
        } else {
            Response::success("No products found matching the query", [], 200);
        }
    }
}
?>
