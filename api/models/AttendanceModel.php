<?php

class AttendanceModel
{
    private $db;
    private $table = 'attendance_logs';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getLastLogToday($employeeId)
    {
        $today = date('Y-m-d');
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE employee_id = :employee_id 
                  AND DATE(event_time) = :today 
                  ORDER BY event_time DESC 
                  LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':employee_id', $employeeId);
        $stmt->bindParam(':today', $today);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    public function insert($employeeId, $deviceCode, $status, $rawName = null)
    {
        $query = "INSERT INTO " . $this->table . " 
                  (employee_id, device_code, event_time, status, raw_name) 
                  VALUES (:employee_id, :device_code, NOW(), :status, :raw_name)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':employee_id', $employeeId);
        $stmt->bindParam(':device_code', $deviceCode);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':raw_name', $rawName);
        
        return $stmt->execute();
    }

    public function getLogsByDateRange($startDate, $endDate = null, $employeeId = null)
    {
        $query = "SELECT al.*, e.code as employee_code, e.name as employee_name 
                  FROM " . $this->table . " al
                  JOIN employees e ON al.employee_id = e.id
                  WHERE DATE(al.event_time) >= :start_date";
        
        if ($endDate) {
            $query .= " AND DATE(al.event_time) <= :end_date";
        }
        
        if ($employeeId) {
            $query .= " AND al.employee_id = :employee_id";
        }
        
        $query .= " ORDER BY al.event_time DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':start_date', $startDate);
        
        if ($endDate) {
            $stmt->bindParam(':end_date', $endDate);
        }
        
        if ($employeeId) {
            $stmt->bindParam(':employee_id', $employeeId);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
