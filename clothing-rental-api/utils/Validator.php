<?php
class Validator {
    public static function validateRequired($data, $fields) {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
            }
        }
        return $errors;
    }

    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function validateNumeric($value) {
        return is_numeric($value);
    }
    
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    public static function validateEnum($value, $allowedValues) {
        return in_array($value, $allowedValues);
    }
    
    public static function validateMinLength($value, $minLength) {
        return strlen(trim($value)) >= $minLength;
    }
}
?>
