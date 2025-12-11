<?php

class EmployeeModel
{
    private $db;
    private $table = 'employees';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findByCode($code)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE code = :code AND is_active = 1 LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    public function getAll($isActive = true)
    {
        $query = "SELECT * FROM " . $this->table;
        if ($isActive !== null) {
            $query .= " WHERE is_active = :is_active";
        }
        $query .= " ORDER BY name ASC";
        
        $stmt = $this->db->prepare($query);
        if ($isActive !== null) {
            $stmt->bindParam(':is_active', $isActive, PDO::PARAM_BOOL);
        }
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
