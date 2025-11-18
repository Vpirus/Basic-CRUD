<?php
class Participant {
    private $conn;
    private $table = "participants";
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function create($data) {
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} (evCode, partFName, partLName, partDRate) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("issd", 
            $data['evCode'],
            $data['partFName'], 
            $data['partLName'], 
            $data['partDRate']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create participant: " . $stmt->error);
        }
        
        $id = $this->conn->insert_id;
        $stmt->close();
        return $id;
    }
    
    public function update($id, $data) {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table} SET evCode = ?, partFName = ?, partLName = ?, partDRate = ? WHERE partID = ?"
        );
        $stmt->bind_param("issdi", 
            $data['evCode'],
            $data['partFName'], 
            $data['partLName'], 
            $data['partDRate'],
            $id
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update participant: " . $stmt->error);
        }
        
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }
    
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE partID = ?");
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete participant: " . $stmt->error);
        }
        
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }
    
    public function getAll() {
        $result = $this->conn->query(
            "SELECT p.*, e.evName 
             FROM {$this->table} p 
             LEFT JOIN events e ON p.evCode = e.evCode 
             ORDER BY p.partLName, p.partFName"
        );
        
        if (!$result) {
            throw new Exception("Failed to fetch participants: " . $this->conn->error);
        }
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE partID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $participant = $result->fetch_assoc();
        $stmt->close();
        
        if (!$participant) {
            throw new Exception("Participant not found");
        }
        
        return $participant;
    }
}
?>