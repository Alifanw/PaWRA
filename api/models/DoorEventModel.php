<?php

class DoorEventModel
{
    private $db;
    private $table = 'door_events';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function insert($deviceCode, $status, $httpCode = null, $response = null, $error = null, $employeeCode = null)
    {
        $query = "INSERT INTO " . $this->table . " 
                  (device_code, status, event_time, processed, http_code, response_message, error_message, employee_code) 
                  VALUES (:device_code, :status, NOW(), 0, :http_code, :response, :error, :employee_code)";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':device_code', $deviceCode);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':http_code', $httpCode);
            $stmt->bindParam(':response', $response);
            $stmt->bindParam(':error', $error);
            $stmt->bindParam(':employee_code', $employeeCode);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            // Jika kolom tidak ada, coba insert tanpa kolom tambahan
            $querySimple = "INSERT INTO " . $this->table . " 
                          (device_code, status, event_time, processed) 
                          VALUES (:device_code, :status, NOW(), 0)";
            
            $stmt = $this->db->prepare($querySimple);
            $stmt->bindParam(':device_code', $deviceCode);
            $stmt->bindParam(':status', $status);
            
            return $stmt->execute();
        }
    }

    public function markAsProcessed($id)
    {
        $query = "UPDATE " . $this->table . " SET processed = 1 WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function getUnprocessedEvents($limit = 100)
    {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE processed = 0 
                  ORDER BY event_time ASC 
                  LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
