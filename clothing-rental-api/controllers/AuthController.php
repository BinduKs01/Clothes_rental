<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/jwt.php';

use \Firebase\JWT\JWT;

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function register($data) {
        $errors = Validator::validateRequired($data, ['name', 'email', 'password']);
        if (!empty($errors)) {
            Response::error("Validation failed", $errors, 422);
        }

        if (!Validator::validateEmail($data['email'])) {
            Response::error("Validation failed", ['email' => 'Invalid email format'], 422);
        }

        if (!Validator::validateMinLength($data['password'], 6)) {
            Response::error("Validation failed", ['password' => 'Password must be at least 6 characters'], 422);
        }

        $this->user->name = $data['name'];
        $this->user->email = $data['email'];
        $this->user->password = $data['password'];

        if ($this->user->emailExists()) {
            Response::error("Validation failed", ['email' => 'Email already exists'], 422);
        }

        if ($this->user->create()) {
            Response::success("User registered successfully", [], 201);
        } else {
            Response::error("Unable to register user", [], 500);
        }
    }

    public function login($data) {
        $errors = Validator::validateRequired($data, ['email', 'password']);
        if (!empty($errors)) {
            Response::error("Validation failed", $errors, 422);
        }

        $this->user->email = $data['email'];
        $email_exists = $this->user->emailExists();

        if ($email_exists && password_verify($data['password'], $this->user->password)) {
            $token = array(
                "iss" => "clothing-rental-api",
                "aud" => "clothing-rental-users",
                "iat" => time(),
                "nbf" => time(),
                "exp" => time() + JWT_EXPIRATION_TIME,
                "data" => array(
                    "user_id" => $this->user->id,
                    "email" => $this->user->email
                )
            );

            $jwt = JWT::encode($token, JWT_SECRET_KEY, 'HS256');
            Response::success("Login successful", [
                'token' => $jwt,
                'user' => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email
                ]
            ], 200);
        } else {
            Response::error("Invalid credentials", [], 401);
        }
    }
}
?>
