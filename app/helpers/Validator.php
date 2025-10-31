<?php

/**
 * Input Validation Helper
 * Provides comprehensive validation for user inputs
 */
class Validator {
    
    /**
     * Validate email format
     */
    public static function email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (Indonesian format)
     * Accepts: 08xxxxxxxxxx, +628xxxxxxxxxx, 628xxxxxxxxxx
     */
    public static function phone($phone) {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        return preg_match('/^(\+62|62|0)[0-9]{9,12}$/', $phone);
    }
    
    /**
     * Validate required fields
     */
    public static function required($value) {
        if (is_string($value)) {
            return trim($value) !== '';
        }
        return !empty($value);
    }
    
    /**
     * Validate minimum length
     */
    public static function minLength($value, $min) {
        return strlen(trim($value)) >= $min;
    }
    
    /**
     * Validate maximum length
     */
    public static function maxLength($value, $max) {
        return strlen(trim($value)) <= $max;
    }
    
    /**
     * Validate numeric value
     */
    public static function numeric($value) {
        return is_numeric($value);
    }
    
    /**
     * Validate positive number
     */
    public static function positive($value) {
        return is_numeric($value) && $value > 0;
    }
    
    /**
     * Validate integer
     */
    public static function integer($value) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    
    /**
     * Validate float/decimal
     */
    public static function decimal($value, $decimals = 2) {
        return is_numeric($value) && preg_match('/^\d+(\.\d{1,' . $decimals . '})?$/', $value);
    }
    
    /**
     * Validate date format (Y-m-d)
     */
    public static function date($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Validate datetime format (Y-m-d H:i:s)
     */
    public static function datetime($datetime) {
        $d = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        return $d && $d->format('Y-m-d H:i:s') === $datetime;
    }
    
    /**
     * Validate string is in allowed values
     */
    public static function inArray($value, array $allowed) {
        return in_array($value, $allowed, true);
    }
    
    /**
     * Validate barcode format
     */
    public static function barcode($barcode) {
        return preg_match('/^[A-Z0-9]{8,20}$/', $barcode);
    }
    
    /**
     * Validate SKU format
     */
    public static function sku($sku) {
        return preg_match('/^[A-Z0-9\-]{3,20}$/', $sku);
    }
    
    /**
     * Validate price (positive decimal with max 2 decimals)
     */
    public static function price($price) {
        return is_numeric($price) && $price >= 0 && preg_match('/^\d+(\.\d{1,2})?$/', $price);
    }
    
    /**
     * Validate stock quantity (non-negative integer)
     */
    public static function stock($stock) {
        return filter_var($stock, FILTER_VALIDATE_INT) !== false && $stock >= 0;
    }
    
    /**
     * Validate username (alphanumeric and underscore, 3-20 chars)
     */
    public static function username($username) {
        return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
    }
    
    /**
     * Validate password strength
     * At least 8 characters, 1 uppercase, 1 lowercase, 1 number
     */
    public static function password($password) {
        return strlen($password) >= 8 
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/[0-9]/', $password);
    }
    
    /**
     * Sanitize string input
     */
    public static function sanitizeString($value) {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize HTML input (for rich text editors)
     */
    public static function sanitizeHtml($value) {
        // Allow only safe HTML tags
        $allowed_tags = '<p><br><strong><em><u><ul><ol><li><a>';
        return strip_tags($value, $allowed_tags);
    }
    
    /**
     * Validate multiple fields at once
     * 
     * @param array $data The data to validate
     * @param array $rules The validation rules
     * @return array|true Returns true if valid, array of errors if invalid
     * 
     * Example:
     * $rules = [
     *     'email' => ['required', 'email'],
     *     'name' => ['required', 'minLength:3'],
     *     'age' => ['required', 'integer', 'positive']
     * ];
     */
    public static function validate(array $data, array $rules) {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            
            foreach ($fieldRules as $rule) {
                // Parse rule and parameters
                $params = [];
                if (strpos($rule, ':') !== false) {
                    list($rule, $paramString) = explode(':', $rule, 2);
                    $params = explode(',', $paramString);
                }
                
                // Apply validation
                $isValid = false;
                switch ($rule) {
                    case 'required':
                        $isValid = self::required($value);
                        $errorMsg = "$field is required";
                        break;
                        
                    case 'email':
                        $isValid = empty($value) || self::email($value);
                        $errorMsg = "$field must be a valid email";
                        break;
                        
                    case 'phone':
                        $isValid = empty($value) || self::phone($value);
                        $errorMsg = "$field must be a valid phone number";
                        break;
                        
                    case 'minLength':
                        $min = $params[0] ?? 1;
                        $isValid = empty($value) || self::minLength($value, $min);
                        $errorMsg = "$field must be at least $min characters";
                        break;
                        
                    case 'maxLength':
                        $max = $params[0] ?? 255;
                        $isValid = empty($value) || self::maxLength($value, $max);
                        $errorMsg = "$field must not exceed $max characters";
                        break;
                        
                    case 'numeric':
                        $isValid = empty($value) || self::numeric($value);
                        $errorMsg = "$field must be numeric";
                        break;
                        
                    case 'integer':
                        $isValid = empty($value) || self::integer($value);
                        $errorMsg = "$field must be an integer";
                        break;
                        
                    case 'positive':
                        $isValid = empty($value) || self::positive($value);
                        $errorMsg = "$field must be positive";
                        break;
                        
                    case 'price':
                        $isValid = empty($value) || self::price($value);
                        $errorMsg = "$field must be a valid price";
                        break;
                        
                    case 'stock':
                        $isValid = self::stock($value);
                        $errorMsg = "$field must be a valid stock quantity";
                        break;
                        
                    case 'date':
                        $isValid = empty($value) || self::date($value);
                        $errorMsg = "$field must be a valid date (Y-m-d)";
                        break;
                        
                    case 'in':
                        $allowed = $params;
                        $isValid = empty($value) || self::inArray($value, $allowed);
                        $errorMsg = "$field must be one of: " . implode(', ', $allowed);
                        break;
                        
                    case 'username':
                        $isValid = empty($value) || self::username($value);
                        $errorMsg = "$field must be alphanumeric and 3-20 characters";
                        break;
                        
                    case 'password':
                        $isValid = empty($value) || self::password($value);
                        $errorMsg = "$field must be at least 8 characters with uppercase, lowercase, and number";
                        break;
                        
                    default:
                        $isValid = true;
                }
                
                if (!$isValid) {
                    $errors[$field] = $errorMsg;
                    break; // Stop checking other rules for this field
                }
            }
        }
        
        return empty($errors) ? true : $errors;
    }
}

