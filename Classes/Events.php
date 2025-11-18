<?php
class Events {
    private $conn;
    private $table = "events";
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function create($data) {
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} (evName, evDate, evVenue, evRFree) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("sssd", 
            $data['evName'], 
            $data['evDate'], 
            $data['evVenue'], 
            $data['evRFree']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create event: " . $stmt->error);
        }
        
        $stmt->close();
        return $this->conn->insert_id;
    }
    
    public function update($id, $data) {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table} SET evName = ?, evDate = ?, evVenue = ?, evRFree = ? WHERE evCode = ?"
        );
        $stmt->bind_param("sssdi", 
            $data['evName'], 
            $data['evDate'], 
            $data['evVenue'], 
            $data['evRFree'],
            $id
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update event: " . $stmt->error);
        }
        
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }
    
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE evCode = ?");
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete event: " . $stmt->error);
        }
        
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }
    
    public function getAll() {
        $result = $this->conn->query("SELECT * FROM {$this->table} ORDER BY evDate DESC");
        if (!$result) {
            throw new Exception("Failed to fetch events: " . $this->conn->error);
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE evCode = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $event = $result->fetch_assoc();
        $stmt->close();
        
        if (!$event) {
            throw new Exception("Event not found");
        }
        
        return $event;
    }
}
?>