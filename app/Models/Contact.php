<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Contact extends Model
{
    protected $table = 'contacts';
    
    protected $fillable = [
        'name', 'email', 'subject', 'message', 'status', 'priority', 
        'assigned_to', 'admin_notes', 'ip_address', 'user_agent'
    ];
    
    /**
     * Get all contacts with pagination and filtering
     * 
     * @param array $filters
     * @return array
     */
    public function getContacts($filters = [])
    {
        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 20;
        $status = $filters['status'] ?? null;
        $priority = $filters['priority'] ?? null;
        $assignedTo = $filters['assigned_to'] ?? null;
        $search = $filters['search'] ?? null;
        
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT c.*, u.username as assigned_username 
                FROM {$this->table} c
                LEFT JOIN users u ON c.assigned_to = u.id
                WHERE 1=1";
        
        $params = [];
        
        if ($status) {
            $sql .= " AND c.status = :status";
            $params[':status'] = $status;
        }
        
        if ($priority) {
            $sql .= " AND c.priority = :priority";
            $params[':priority'] = $priority;
        }
        
        if ($assignedTo) {
            $sql .= " AND c.assigned_to = :assigned_to";
            $params[':assigned_to'] = $assignedTo;
        }
        
        if ($search) {
            $sql .= " AND (c.name LIKE :search OR c.email LIKE :search OR c.subject LIKE :search OR c.message LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        $sql .= " ORDER BY 
                    CASE c.priority 
                        WHEN 'urgent' THEN 1 
                        WHEN 'high' THEN 2 
                        WHEN 'medium' THEN 3 
                        WHEN 'low' THEN 4 
                    END ASC,
                    c.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        $this->db->bind(':limit', $perPage, \PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, \PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }
    
    /**
     * Get total count of contacts with filters
     * 
     * @param array $filters
     * @return int
     */
    public function getContactsCount($filters = [])
    {
        $status = $filters['status'] ?? null;
        $priority = $filters['priority'] ?? null;
        $assignedTo = $filters['assigned_to'] ?? null;
        $search = $filters['search'] ?? null;
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($status) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }
        
        if ($priority) {
            $sql .= " AND priority = :priority";
            $params[':priority'] = $priority;
        }
        
        if ($assignedTo) {
            $sql .= " AND assigned_to = :assigned_to";
            $params[':assigned_to'] = $assignedTo;
        }
        
        if ($search) {
            $sql .= " AND (name LIKE :search OR email LIKE :search OR subject LIKE :search OR message LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        $result = $this->db->single();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get contact statistics
     * 
     * @return array
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'new' THEN 1 END) as new_count,
                    COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress_count,
                    COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_count,
                    COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed_count,
                    COUNT(CASE WHEN priority = 'urgent' THEN 1 END) as urgent_count,
                    COUNT(CASE WHEN priority = 'high' THEN 1 END) as high_count,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as today_count,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as week_count
                FROM {$this->table}";
        
        $this->db->query($sql);
        return $this->db->single();
    }
    
    /**
     * Update contact status
     * 
     * @param int $id
     * @param string $status
     * @param int|null $assignedTo
     * @return bool
     */
    public function updateStatus($id, $status, $assignedTo = null)
    {
        $updateData = ['status' => $status];
        
        if ($assignedTo !== null) {
            $updateData['assigned_to'] = $assignedTo;
        }
        
        // Set resolved/closed timestamps
        if ($status === 'resolved' && !$this->getResolvedAt($id)) {
            $updateData['resolved_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'closed' && !$this->getClosedAt($id)) {
            $updateData['closed_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($id, $updateData);
    }
    
    /**
     * Add admin notes to contact
     * 
     * @param int $id
     * @param string $notes
     * @return bool
     */
    public function addAdminNotes($id, $notes)
    {
        $contact = $this->find($id);
        if (!$contact) {
            return false;
        }
        
        $existingNotes = $contact['admin_notes'] ?? '';
        $timestamp = date('Y-m-d H:i:s');
        $adminName = $_SESSION['username'] ?? 'Admin';
        
        $newNote = "[{$timestamp} - {$adminName}] {$notes}";
        
        if (!empty($existingNotes)) {
            $newNote = $existingNotes . "\n\n" . $newNote;
        }
        
        return $this->update($id, ['admin_notes' => $newNote]);
    }
    
    /**
     * Get recent contacts for dashboard
     * 
     * @param int $limit
     * @return array
     */
    public function getRecent($limit = 5)
    {
        $sql = "SELECT c.*, u.username as assigned_username 
                FROM {$this->table} c
                LEFT JOIN users u ON c.assigned_to = u.id
                ORDER BY c.created_at DESC 
                LIMIT :limit";
        
        $this->db->query($sql);
        $this->db->bind(':limit', $limit, \PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }
    
    /**
     * Get resolved timestamp
     * 
     * @param int $id
     * @return string|null
     */
    private function getResolvedAt($id)
    {
        $contact = $this->find($id);
        return $contact['resolved_at'] ?? null;
    }
    
    /**
     * Get closed timestamp
     * 
     * @param int $id
     * @return string|null
     */
    private function getClosedAt($id)
    {
        $contact = $this->find($id);
        return $contact['closed_at'] ?? null;
    }
}
