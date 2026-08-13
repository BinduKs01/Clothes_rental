<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class OrderController {
    private $db;
    private $order;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->order = new Order($this->db);
    }

    public function create($data) {
        $userData = AuthMiddleware::authenticate();
        $user_id = $userData->user_id;

        $errors = Validator::validateRequired($data, ['product_id', 'rental_start', 'rental_end']);
        if (!empty($errors)) {
            Response::error("Validation failed", $errors, 422);
        }

        if (!Validator::validateDate($data['rental_start']) || !Validator::validateDate($data['rental_end'])) {
            Response::error("Validation failed", ['date' => 'Invalid date format. Use YYYY-MM-DD'], 422);
        }

        $start = new DateTime($data['rental_start']);
        $end = new DateTime($data['rental_end']);
        
        if ($start > $end) {
            Response::error("Validation failed", ['rental_end' => 'End date cannot be before start date'], 422);
        }

        $product = new Product($this->db);
        $product->id = $data['product_id'];
        $productData = $product->readOne();

        if ($productData == null) {
            Response::error("Validation failed", ['product_id' => 'Product does not exist'], 422);
        }

        if ($productData['status'] !== 'available') {
            Response::error("Product is currently not available for rent", [], 400);
        }

        $interval = $start->diff($end);
        $days = $interval->days + 1;
        $total_amount = $days * $productData['price_per_day'];

        $this->order->user_id = $user_id;
        $this->order->product_id = $data['product_id'];
        $this->order->rental_start = $data['rental_start'];
        $this->order->rental_end = $data['rental_end'];
        $this->order->total_amount = $total_amount;
        $this->order->status = 'pending';

        if ($this->order->create()) {
            $product->status = 'rented';
            $product->updateStatus();

            Response::success("Order created successfully", ['id' => $this->order->id, 'total_amount' => $total_amount], 201);
        } else {
            Response::error("Unable to create order", [], 500);
        }
    }

    public function getByUser() {
        $userData = AuthMiddleware::authenticate();
        $this->order->user_id = $userData->user_id;

        $stmt = $this->order->readByUser();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $orders_arr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($orders_arr, $row);
            }
            Response::success("Orders retrieved successfully", $orders_arr, 200);
        } else {
            Response::success("No orders found", [], 200);
        }
    }

    public function getOne($id) {
        AuthMiddleware::authenticate();

        $this->order->id = $id;
        $row = $this->order->readOne();

        if ($row != null) {
            Response::success("Order retrieved successfully", $row, 200);
        } else {
            Response::error("Order not found", [], 404);
        }
    }

    public function updateStatus($id, $data) {
        AuthMiddleware::authenticate();

        $errors = Validator::validateRequired($data, ['status']);
        if (!empty($errors)) {
            Response::error("Validation failed", $errors, 422);
        }

        $allowedStatuses = ['pending', 'approved', 'rented', 'returned', 'cancelled'];
        if (!Validator::validateEnum($data['status'], $allowedStatuses)) {
            Response::error("Validation failed", ['status' => 'Invalid status'], 422);
        }

        $this->order->id = $id;
        $orderData = $this->order->readOne();

        if ($orderData == null) {
            Response::error("Order not found", [], 404);
        }

        $this->order->status = $data['status'];

        if ($this->order->updateStatus()) {
            if ($data['status'] === 'returned' || $data['status'] === 'cancelled') {
                $product = new Product($this->db);
                $product->id = $orderData['product_id'];
                $product->status = 'available';
                $product->updateStatus();
            } else if ($data['status'] === 'rented' || $data['status'] === 'approved' || $data['status'] === 'pending') {
                $product = new Product($this->db);
                $product->id = $orderData['product_id'];
                $product->status = 'rented';
                $product->updateStatus();
            }

            Response::success("Order status updated successfully", [], 200);
        } else {
            Response::error("Unable to update order status", [], 500);
        }
    }
}
?>
