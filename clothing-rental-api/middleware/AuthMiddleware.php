<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../utils/Response.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class AuthMiddleware {
    public static function authenticate() {
        $headers = apache_request_headers();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        
        // Also check HTTP_AUTHORIZATION fallback
        if (!$authHeader && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        }

        if (!$authHeader) {
            Response::error("Unauthorized: No token provided.", [], 401);
        }

        $arr = explode(" ", $authHeader);
        if (count($arr) < 2 || $arr[0] !== 'Bearer') {
            Response::error("Unauthorized: Invalid token format.", [], 401);
        }

        $jwt = $arr[1];

        try {
            $decoded = JWT::decode($jwt, new Key(JWT_SECRET_KEY, 'HS256'));
            return $decoded->data; // Contains user info like user_id, email
        } catch (Exception $e) {
            Response::error("Unauthorized: " . $e->getMessage(), [], 401);
        }
    }
}
?>
