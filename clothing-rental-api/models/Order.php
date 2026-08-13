<?php
class Order {
    private $conn;
    private $table_name = "orders";

    public $id;
    public $user_id;
    public $product_id;
    public $rental_start;
    public $rental_end;
    public $total_amount;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id=:user_id, product_id=:product_id, rental_start=:rental_start, 
                      rental_end=:rental_end, total_amount=:total_amount, status=:status";
        $stmt = $this->conn->prepare($query);

        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->rental_start = htmlspecialchars(strip_tags($this->rental_start));
        $this->rental_end = htmlspecialchars(strip_tags($this->rental_end));
        $this->total_amount = htmlspecialchars(strip_tags($this->total_amount));
        $this->status = htmlspecialchars(strip_tags($this->status));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":rental_start", $this->rental_start);
        $stmt->bindParam(":rental_end", $this->rental_end);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function readByUser() {
        $query = "SELECT o.*, p.name as product_name, p.image as product_image 
                  FROM " . $this->table_name . " o 
                  LEFT JOIN products p ON o.product_id = p.id 
                  WHERE o.user_id = ? 
                  ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->user_id);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT o.*, p.name as product_name, p.image as product_image 
                  FROM " . $this->table_name . " o 
                  LEFT JOIN products p ON o.product_id = p.id 
                  WHERE o.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->user_id = $row['user_id'];
            $this->product_id = $row['product_id'];
            $this->rental_start = $row['rental_start'];
            $this->rental_end = $row['rental_end'];
            $this->total_amount = $row['total_amount'];
            $this->status = $row['status'];
            return $row;
        }
        return null;
    }

    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " SET status=:status WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
