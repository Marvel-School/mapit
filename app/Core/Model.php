<?php

namespace App\Core;

use App\Core\Database;

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    
    /**
     * Create a new model instance
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find a record by ID
     * 
     * @param int $id
     * @return array|bool
     */
    public function find($id)
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $this->db->bind(':id', $id);
        
        return $this->db->single();
    }
    
    /**
     * Find all records
     * 
     * @return array
     */
    public function all()
    {
        $this->db->query("SELECT * FROM {$this->table}");
        return $this->db->resultSet();
    }
    
    /**
     * Find records by a specific field
     * 
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public function where($field, $value)
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE {$field} = :value");
        $this->db->bind(':value', $value);
        
        return $this->db->resultSet();
    }
    
    /**
     * Find a single record by a specific field
     * 
     * @param string $field
     * @param mixed $value
     * @return array|bool
     */
    public function findBy($field, $value)
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE {$field} = :value");
        $this->db->bind(':value', $value);
        
        return $this->db->single();
    }
    
    /**
     * Create a new record
     * 
     * @param array $data
     * @return int|bool
     */
    public function create(array $data)
    {
        // Filter data to only include fillable fields
        $data = $this->filterFillable($data);
        
        if (empty($data)) {
            return false;
        }
        
        $fields = array_keys($data);
        
        // Prepare SQL
        $fieldsString = implode(', ', $fields);
        $placeholders = ':' . implode(', :', $fields);
        
        $this->db->query("INSERT INTO {$this->table} ({$fieldsString}) VALUES ({$placeholders})");
        
        // Bind values
        foreach ($data as $key => $value) {
            $this->db->bind(":{$key}", $value);
        }
        
        // Execute
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update a record
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data)
    {
        // Filter data to only include fillable fields
        $data = $this->filterFillable($data);
        
        if (empty($data)) {
            return false;
        }
        
        // Prepare SET clause
        $setClause = '';
        foreach ($data as $key => $value) {
            $setClause .= "{$key} = :{$key}, ";
        }
        $setClause = rtrim($setClause, ', ');
        
        // Prepare SQL
        $this->db->query("UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id");
        
        // Bind values
        foreach ($data as $key => $value) {
            $this->db->bind(":{$key}", $value);
        }
        $this->db->bind(':id', $id);
        
        // Execute
        return $this->db->execute();
    }
    
    /**
     * Delete a record
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $this->db->query("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }
    
    /**
     * Count records
     * 
     * @return int
     */
    public function count()
    {
        $this->db->query("SELECT COUNT(*) as count FROM {$this->table}");
        $result = $this->db->single();
        
        return (int) $result['count'];
    }
    
    /**
     * Count records with condition
     * 
     * @param string $field
     * @param mixed $value
     * @return int
     */
    public function countWhere($field, $value)
    {
        $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE {$field} = :value");
        $this->db->bind(':value', $value);
        
        $result = $this->db->single();
        
        return (int) $result['count'];
    }
    
    /**
     * Filter data to only include fillable fields
     * 
     * @param array $data
     * @return array
     */
    protected function filterFillable(array $data)
    {
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool
     */
    public function commit()
    {
        return $this->db->endTransaction();
    }
    
    /**
     * Rollback a transaction
     * 
     * @return bool
     */
    public function rollback()
    {
        return $this->db->cancelTransaction();
    }
}
