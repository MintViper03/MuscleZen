<?php
class Validation {
    // Email validation
    public static function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        return true;
    }

    // Date validation
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        if (!$d || $d->format($format) !== $date) {
            throw new Exception('Invalid date format');
        }
        return true;
    }

    // Numeric range validation
    public static function validateRange($value, $min, $max, $fieldName) {
        $value = filter_var($value, FILTER_VALIDATE_FLOAT);
        if ($value === false || $value < $min || $value > $max) {
            throw new Exception("$fieldName must be between $min and $max");
        }
        return true;
    }

    // Required fields validation
    public static function validateRequired($data, $fields) {
        $missing = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing[] = $field;
            }
        }
        if (!empty($missing)) {
            throw new Exception('Required fields missing: ' . implode(', ', $missing));
        }
        return true;
    }

    // File upload validation
    public static function validateFile($file, $allowedTypes, $maxSize) {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Invalid file parameters');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('File too large');
            case UPLOAD_ERR_NO_FILE:
                throw new Exception('No file uploaded');
            default:
                throw new Exception('Unknown upload error');
        }

        if ($file['size'] > $maxSize) {
            throw new Exception('File too large');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Invalid file type');
        }

        return true;
    }
}
?>
