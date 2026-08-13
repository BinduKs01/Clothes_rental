<?php
class Wishlist {
    private $conn;
    private $table_name = "wishlist";

    public $id;
    public $user_id;
    public $product_id;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET user_id=:user_id, product_id=:product_id";
        $stmt = $this->conn->prepare($query);

        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":product_id", $this->product_id);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function readByUser() {
        $query = "SELECT w.id, w.product_id, w.created_at, p.name, p.description, p.price_per_day, p.image, p.status 
                  FROM " . $this->table_name . " w 
                  LEFT JOIN products p ON w.product_id = p.id 
                  WHERE w.user_id = ? 
                  ORDER BY w.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->user_id);
        $stmt->execute();
        return $stmt;
    }

    public function exists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE user_id = ? AND product_id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->user_id);
        $stmt->bindParam(2, $this->product_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $this->user_id);
        
        if($stmt->execute() && $stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }
}
?>
