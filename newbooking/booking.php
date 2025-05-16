<?php
class Bookings {
    private $conn;
    private $table = 'bookings';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllBookings() {
        try {
            $query = "SELECT b.*, CONCAT(p.first_name, ' ', p.last_name) as passenger_name, 
                      CONCAT(bs.location, ' to ', bs.destination) as bus_route
                      FROM " . $this->table . " b
                      LEFT JOIN passenger p ON b.passenger_id = p.passenger_id
                      LEFT JOIN bus bs ON b.bus_id = bs.bus_id
                      ORDER BY b.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getAllBookings: " . $e->getMessage());
            return [];
        }
    }

    public function getBookingByReference($reference) {
        try {
            $query = "SELECT b.*, CONCAT(p.first_name, ' ', p.last_name) as passenger_name, 
                      CONCAT(bs.location, ' to ', bs.destination) as bus_route
                      FROM " . $this->table . " b
                      LEFT JOIN passenger p ON b.passenger_id = p.passenger_id
                      LEFT JOIN bus bs ON b.bus_id = bs.bus_id
                      WHERE b.reference = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $reference);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getBookingByReference: " . $e->getMessage());
            return false;
        }
    }

    public function getFilteredBookings($filters) {
        try {
            $query = "SELECT b.*, CONCAT(p.first_name, ' ', p.last_name) as passenger_name, 
                      CONCAT(bs.location, ' to ', bs.destination) as bus_route
                      FROM " . $this->table . " b
                      LEFT JOIN passenger p ON b.passenger_id = p.passenger_id
                      LEFT JOIN bus bs ON b.bus_id = bs.bus_id
                      WHERE 1=1";
            
            $params = [];
            if (!empty($filters['passenger_type'])) {
                $query .= " AND b.passenger_type = ?";
                $params[] = $filters['passenger_type'];
            }

            $query .= " ORDER BY b.created_at DESC";
            $stmt = $this->conn->prepare($query);

            foreach ($params as $index => $value) {
                $stmt->bindValue($index + 1, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getFilteredBookings: " . $e->getMessage());
            return [];
        }
    }

    public function createBooking($reference, $passenger_id, $bus_id, $reserve_name, $passenger_type, $seat_number, $remarks, $status = 'pending') {
        try {
            $checkQuery = "SELECT COUNT(*) FROM " . $this->table . " WHERE reference = ?";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(1, $reference);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() > 0) {
                return false;
            }
            
            $query = "INSERT INTO " . $this->table . " (reference, passenger_id, bus_id, reserve_name, passenger_type, seat_number, remarks, status)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(1, $reference);
            $stmt->bindParam(2, $passenger_id);
            $stmt->bindParam(3, $bus_id);
            $stmt->bindParam(4, $reserve_name);
            $stmt->bindParam(5, $passenger_type);
            $stmt->bindParam(6, $seat_number);
            $stmt->bindParam(7, $remarks);
            $stmt->bindParam(8, $status);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error in createBooking: " . $e->getMessage());
            return false;
        }
    }

    public function updateBooking($reference, $reserve_name, $passenger_type, $seat_number, $remarks, $status) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET reserve_name = ?, passenger_type = ?, seat_number = ?, remarks = ?, status = ?
                      WHERE reference = ?";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(1, $reserve_name);
            $stmt->bindParam(2, $passenger_type);
            $stmt->bindParam(3, $seat_number);
            $stmt->bindParam(4, $remarks);
            $stmt->bindParam(5, $status);
            $stmt->bindParam(6, $reference);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error in updateBooking: " . $e->getMessage());
            return false;
        }
    }

    public function deleteBookingByReference($reference) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE reference = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $reference);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error in deleteBookingByReference: " . $e->getMessage());
            return false;
        }
    }
}
?>