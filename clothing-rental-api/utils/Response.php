<?php
class Response {
    public static function json($success, $message, $data = [], $errors = [], $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        if ($success) {
            $response['data'] = empty($data) ? new stdClass() : $data;
        } else {
            $response['errors'] = empty($errors) ? new stdClass() : $errors;
        }

        echo json_encode($response);
        exit;
    }

    public static function success($message, $data = [], $statusCode = 200) {
        self::json(true, $message, $data, [], $statusCode);
    }

    public static function error($message, $errors = [], $statusCode = 400) {
        self::json(false, $message, [], $errors, $statusCode);
    }
}
?>
