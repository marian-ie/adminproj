<?php
class Bookings {
    private $conn;
    private $table = 'Bookings';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllBusDetails() {
        try {
            $query = "SELECT * FROM " . $this->table . " ORDER BY reference DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $bookings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $bookings[] = $row;
            }

            return $bookings;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }

    public function getBusById($reference) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE reference = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $reference);
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

            if (!empty($filters['passenger_type'])) {
                $query .= " AND passenger_type = ?";
                $params[] = $filters['passenger_type'];
            }

            $query .= " ORDER BY reference DESC";
            $stmt = $this->conn->prepare($query);

            foreach ($params as $index => $value) {
                $stmt->bindValue($index + 1, $value);
            }

            $stmt->execute();

            $bookings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $bookings[] = $row;
            }

            return $bookings;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }

    public function createBooking($reference, $passenger_type, $seat_number, $price, $status) {
        try {
            // First check if this reference already exists
            $checkQuery = "SELECT COUNT(*) FROM " . $this->table . " WHERE reference = ?";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(1, $reference);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() > 0) {
                // Reference already exists, return false
                return false;
            }
            
            // If reference doesn't exist, insert new booking
            $query = "INSERT INTO " . $this->table . " (reference, passenger_type, seat_number, price, status)
                      VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(1, $reference);
            $stmt->bindParam(2, $passenger_type);
            $stmt->bindParam(3, $seat_number);
            $stmt->bindParam(4, $price);
            $stmt->bindParam(5, $status);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function updateBooking($reference, $passenger_type, $seat_number, $price, $status) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET passenger_type = ?, seat_number = ?, price = ?, status = ?
                      WHERE reference = ?";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(1, $passenger_type);
            $stmt->bindParam(2, $seat_number);
            $stmt->bindParam(3, $price);
            $stmt->bindParam(4, $status);
            $stmt->bindParam(5, $reference);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteBus($booking_id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE booking_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $booking_id);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
}
?>