<?php

namespace App\Models;

use App\Core\Model;

class Log extends Model
{
    protected $table = 'logs';
    protected $fillable = [
        'level', 'message', 'data', 'component', 'url'
    ];
    
    /**
     * Get logs with pagination
     * 
     * @param int $page
     * @param int $perPage
     * @param array $filters
     * @return array
     */    public function getPaginated($page = 1, $perPage = 20, $filters = [])
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM logs";
        $params = [];
        
        // Build WHERE conditions
        $whereConditions = [];
        
        if (!empty($filters['level'])) {
            $whereConditions[] = "level = :level";
            $params[':level'] = $filters['level'];
        }
        
        if (!empty($filters['component'])) {
            $whereConditions[] = "component LIKE :component";
            $params[':component'] = '%' . $filters['component'] . '%';
        }
        
        if (!empty($filters['from_date'])) {
            $whereConditions[] = "created_at >= :from_date";
            $params[':from_date'] = $filters['from_date'] . ' 00:00:00';
        }
        
        if (!empty($filters['to_date'])) {
            $whereConditions[] = "created_at <= :to_date";
            $params[':to_date'] = $filters['to_date'] . ' 23:59:59';
        }
        
        // Only add WHERE clause if we have conditions
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }
          // Add ordering and limit
        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $params[':offset'] = $offset;
        $params[':limit'] = $perPage;
          $this->db->query($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            if ($key === ':offset' || $key === ':limit') {
                $this->db->bind($key, (int)$value, \PDO::PARAM_INT);
            } else {
                $this->db->bind($key, $value);
            }
        }
        
        // Get the logs
        $logs = $this->db->resultSet();
          // Count total logs for pagination
        $countSql = "SELECT COUNT(*) as count FROM logs";
        
        // Only add WHERE clause if we have conditions
        if (!empty($whereConditions)) {
            $countSql .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $this->db->query($countSql);
        
        // Bind parameters for count query (except pagination)
        foreach ($params as $key => $value) {
            if ($key !== ':offset' && $key !== ':limit') {
                $this->db->bind($key, $value);
            }
        }
        
        $countResult = $this->db->single();
        $totalCount = $countResult['count'];
        
        return [
            'logs' => $logs,
            'total' => $totalCount,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($totalCount / $perPage)
        ];
    }
    
    /**
     * Get log levels
     * 
     * @return array
     */
    public function getLevels()
    {
        $this->db->query("SELECT DISTINCT level FROM logs ORDER BY level");
        $levels = $this->db->resultSet();
        
        return array_map(function($level) {
            return $level['level'];
        }, $levels);
    }
    
    /**
     * Get log components
     * 
     * @return array
     */
    public function getComponents()
    {
        $this->db->query("SELECT DISTINCT component FROM logs WHERE component IS NOT NULL ORDER BY component");
        $components = $this->db->resultSet();
        
        return array_map(function($component) {
            return $component['component'];
        }, $components);
    }
    
    /**
     * Write a log entry
     * 
     * @param string $level
     * @param string $message
     * @param array $data
     * @param string $component
     * @return int|bool
     */    public static function write($level, $message, $data = [], $component = null)
    {
        $log = new self();
          // Truncate component to fit database column (assuming 50 chars max)
        if ($component && strlen($component) > 50) {
            $component = substr($component ?? '', 0, 47) . '...';
        }
        
        return $log->create([
            'level' => $level,
            'message' => $message,
            'data' => is_array($data) || is_object($data) ? json_encode($data) : $data,
            'component' => $component,
            'url' => $_SERVER['REQUEST_URI'] ?? null
        ]);
    }
    
    /**
     * Clear all log entries
     * 
     * @return bool
     */
    public function clearAll()
    {
        $this->db->query("DELETE FROM logs");
        return $this->db->execute();
    }
}
