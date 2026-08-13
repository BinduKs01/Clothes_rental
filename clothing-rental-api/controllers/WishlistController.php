<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Wishlist.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class WishlistController {
    private $db;
    private $wishlist;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->wishlist = new Wishlist($this->db);
    }

    public function create($data) {
        $userData = AuthMiddleware::authenticate();
        $user_id = $userData->user_id;

        $errors = Validator::validateRequired($data, ['product_id']);
        if (!empty($errors)) {
            Response::error("Validation failed", $errors, 422);
        }

        $product = new Product($this->db);
        $product->id = $data['product_id'];
        if (!$product->exists()) {
            Response::error("Validation failed", ['product_id' => 'Product does not exist'], 422);
        }

        $this->wishlist->user_id = $user_id;
        $this->wishlist->product_id = $data['product_id'];

        if ($this->wishlist->exists()) {
            Response::error("Product already in wishlist", [], 400);
        }

        if ($this->wishlist->create()) {
            Response::success("Product added to wishlist", ['id' => $this->wishlist->id], 201);
        } else {
            Response::error("Unable to add product to wishlist", [], 500);
        }
    }

    public function getByUser() {
        $userData = AuthMiddleware::authenticate();
        $this->wishlist->user_id = $userData->user_id;
        
        $stmt = $this->wishlist->readByUser();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $wishlist_arr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($wishlist_arr, $row);
            }
            Response::success("Wishlist retrieved successfully", $wishlist_arr, 200);
        } else {
            Response::success("Wishlist is empty", [], 200);
        }
    }

    public function delete($id) {
        $userData = AuthMiddleware::authenticate();
        
        $this->wishlist->id = $id;
        $this->wishlist->user_id = $userData->user_id;

        if ($this->wishlist->delete()) {
            Response::success("Product removed from wishlist", [], 200);
        } else {
            Response::error("Unable to remove product from wishlist, or it does not exist", [], 404);
        }
    }
}
?>
