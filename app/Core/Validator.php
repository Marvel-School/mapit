<?php

namespace App\Core;

class Validator
{
    protected $errors = [];
    protected $data = [];
    
    /**
     * Create a new validator instance
     * 
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }
    
    /**
     * Validate the data against the rules
     * 
     * @param array $rules
     * @return bool
     */
    public function validate(array $rules)
    {
        foreach ($rules as $field => $ruleString) {
            $ruleArray = explode('|', $ruleString);
            
            foreach ($ruleArray as $rule) {
                // Check if the rule has parameters
                if (strpos($rule, ':') !== false) {
                    list($ruleName, $ruleParam) = explode(':', $rule, 2);
                } else {
                    $ruleName = $rule;
                    $ruleParam = null;
                }
                
                $method = 'validate' . ucfirst($ruleName);
                
                if (method_exists($this, $method)) {
                    $this->$method($field, $ruleParam);
                }
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Get all validation errors
     * 
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }
    
    /**
     * Get a specific error message
     * 
     * @param string $field
     * @return string|null
     */
    public function error($field)
    {
        return $this->errors[$field] ?? null;
    }
    
    /**
     * Check if the field is required
     * 
     * @param string $field
     * @return void
     */
    protected function validateRequired($field)
    {
        $value = $this->data[$field] ?? null;
        
        if ($value === null || $value === '') {
            $this->errors[$field] = "The {$field} field is required";
        }
    }
    
    /**
     * Check if the field is a valid email
     * 
     * @param string $field
     * @return void
     */
    protected function validateEmail($field)
    {
        $value = $this->data[$field] ?? null;
        
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "The {$field} must be a valid email address";
        }
    }
    
    /**
     * Check if the field has a minimum length
     * 
     * @param string $field
     * @param string $param
     * @return void
     */
    protected function validateMin($field, $param)
    {
        $value = $this->data[$field] ?? null;
        
        if ($value && mb_strlen($value) < $param) {
            $this->errors[$field] = "The {$field} must be at least {$param} characters";
        }
    }
    
    /**
     * Check if the field has a maximum length
     * 
     * @param string $field
     * @param string $param
     * @return void
     */
    protected function validateMax($field, $param)
    {
        $value = $this->data[$field] ?? null;
        
        if ($value && mb_strlen($value) > $param) {
            $this->errors[$field] = "The {$field} may not be greater than {$param} characters";
        }
    }
    
    /**
     * Check if the field matches another field
     * 
     * @param string $field
     * @param string $param
     * @return void
     */
    protected function validateMatch($field, $param)
    {
        $value = $this->data[$field] ?? null;
        $matchValue = $this->data[$param] ?? null;
        
        if ($value !== $matchValue) {
            $this->errors[$field] = "The {$field} and {$param} must match";
        }
    }
    
    /**
     * Check if the field is unique in the database
     * 
     * @param string $field
     * @param string $param
     * @return void
     */
    protected function validateUnique($field, $param)
    {
        $value = $this->data[$field] ?? null;
        
        if ($value) {
            // Parse table and except id if provided
            $params = explode(',', $param);
            $table = $params[0];
            $except = $params[1] ?? null;
            $exceptField = $params[2] ?? 'id';
            
            $db = Database::getInstance();
            
            if ($except) {
                $db->query("SELECT * FROM {$table} WHERE {$field} = :value AND {$exceptField} != :except");
                $db->bind(':value', $value);
                $db->bind(':except', $except);
            } else {
                $db->query("SELECT * FROM {$table} WHERE {$field} = :value");
                $db->bind(':value', $value);
            }
            
            $result = $db->single();
            
            if ($result) {
                $this->errors[$field] = "The {$field} is already taken";
            }
        }
    }
    
    /**
     * Check if the field is numeric
     * 
     * @param string $field
     * @return void
     */
    protected function validateNumeric($field)
    {
        $value = $this->data[$field] ?? null;
        
        if ($value && !is_numeric($value)) {
            $this->errors[$field] = "The {$field} must be a number";
        }
    }
    
    /**
     * Check if the field is a valid URL
     * 
     * @param string $field
     * @return void
     */
    protected function validateUrl($field)
    {
        $value = $this->data[$field] ?? null;
        
        if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field] = "The {$field} must be a valid URL";
        }
    }
}