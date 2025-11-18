<?php
class Registration {
    private $conn;
    private $table = "registrations";
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function create($data) {
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} (partID, regDate, regFPaid, regPMode) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("isds", 
            $data['partID'],
            $data['regDate'], 
            $data['regFPaid'], 
            $data['regPMode']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create registration: " . $stmt->error);
        }
        
        $id = $this->conn->insert_id;
        $stmt->close();
        return $id;
    }
    
    public function update($id, $data) {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table} SET partID = ?, regDate = ?, regFPaid = ?, regPMode = ? WHERE regCode = ?"
        );
        $stmt->bind_param("isdsi", 
            $data['partID'],
            $data['regDate'], 
            $data['regFPaid'], 
            $data['regPMode'],
            $id
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update registration: " . $stmt->error);
        }
        
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }
    
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE regCode = ?");
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete registration: " . $stmt->error);
        }
        
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }
    
    public function getAll() {
        $result = $this->conn->query(
            "SELECT r.*, 
                    p.partFName, p.partLName,
                    e.evName
             FROM {$this->table} r
             LEFT JOIN participants p ON r.partID = p.partID
             LEFT JOIN events e ON p.evCode = e.evCode
             ORDER BY r.regDate DESC"
        );
        
        if (!$result) {
            throw new Exception("Failed to fetch registrations: " . $this->conn->error);
        }
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE regCode = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $registration = $result->fetch_assoc();
        $stmt->close();
        
        if (!$registration) {
            throw new Exception("Registration not found");
        }
        
        return $registration;
    }
}
?>