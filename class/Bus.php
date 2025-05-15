<?php
class Bus {
    private $conn;
    private $table = 'Bus';
    public function __construct($db) {
        $this->conn = $db;
    }

   
    public function getAllBusDetails() {
        try {
            $query = "SELECT * FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $buses = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $buses[] = $row;
            }
            
            return $buses;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }
    
    
    public function getBusById($bus_id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE bus_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $bus_id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
    
    
    public function getFilteredBuses($filters) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE 1=1";
            $params = [];
            
            if (isset($filters['location']) && $filters['location'] !== '') {
                $query .= " AND location = ?";
                $params[] = $filters['location'];
            }
            
            if (isset($filters['destination']) && $filters['destination'] !== '') {
                $query .= " AND destination = ?";
                $params[] = $filters['destination'];
            }
            
            if (isset($filters['bus_type']) && $filters['bus_type'] !== '') {
                $query .= " AND bus_type = ?";
                $params[] = $filters['bus_type'];
            }
            
            $stmt = $this->conn->prepare($query);
            
            for ($i = 0; $i < count($params); $i++) {
                $stmt->bindValue($i + 1, $params[$i]);
            }
            
            $stmt->execute();
            
            $buses = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $buses[] = $row;
            }
            
            return $buses;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }

  
    public function createNewBus($bus_number, $location, $destination, $bus_type, $date, $departure_time, $arrival_time, $available_seats, $price, $status) {
        try {
            $query = "INSERT INTO {$this->table} 
                      (bus_number, location, destination, bus_type, date, departure_time, arrival_time, available_seats, price, status) 
                      VALUES (:bus_number, :location, :destination, :bus_type, :date, :departure_time, :arrival_time, :available_seats, :price, :status)";
            
            $stmt = $this->conn->prepare($query);
    
            // Bind parameters using named placeholders (better for readability and debugging)
            $stmt->bindParam(':bus_number', $bus_number);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':destination', $destination);
            $stmt->bindParam(':bus_type', $bus_type);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':departure_time', $departure_time);
            $stmt->bindParam(':arrival_time', $arrival_time);
            $stmt->bindParam(':available_seats', $available_seats);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':status', $status);
    
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
    
    
  
    public function updateBus($bus_id, $bus_number, $location, $destination, $bus_type, $date, $departure_time, $arrival_time, $available_seats, $price, $status) {
        try {
            $query = "UPDATE " . $this->table . " 
                    SET bus_number = ?, location = ?, destination = ?, bus_type = ?, date = ?, departure_time = ?, arrival_time = ?, 
                        available_seats = ?, price = ?, status = ? 
                    WHERE bus_id = ?";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(1, $bus_number);
            $stmt->bindParam(2, $location);
            $stmt->bindParam(3, $destination);
            $stmt->bindParam(4, $bus_type);
            $stmt->bindParam(5, $date);
            $stmt->bindParam(6, $departure_time);
            $stmt->bindParam(7, $arrival_time);
            $stmt->bindParam(8, $available_seats);
            $stmt->bindParam(9, $price);
            $stmt->bindParam(10, $status);
            $stmt->bindParam(11, $bus_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
    
   
    public function deleteBus($bus_id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE bus_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $bus_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
}