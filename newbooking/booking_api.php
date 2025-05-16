<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    require_once 'database.php';
    require_once '../class/booking.php';

    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        echo json_encode(["status" => "error", "message" => "Database connection failed"]);
        exit;
    }

    $booking = new Bookings($db);
    $method = $_SERVER['REQUEST_METHOD'];
    $inputData = null;

    if ($method === 'POST' || $method === 'PUT') {
        $inputJSON = file_get_contents('php://input');
        $inputData = json_decode($inputJSON, true);
        
        if ($inputData === null && json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
            exit;
        }
    }

    switch ($method) {
        case 'GET':
            $filters = [];
            if (isset($_GET['passenger_type'])) {
                $filters['passenger_type'] = $_GET['passenger_type'];
            }

            if (isset($_GET['reference'])) {
                $bookingData = $booking->getBookingByReference($_GET['reference']);
                echo json_encode($bookingData ? 
                    ["status" => "success", "booking" => $bookingData] : 
                    ["status" => "error", "message" => "Booking not found"]
                );
            } else {
                $result = empty($filters) ? $booking->getAllBookings() : $booking->getFilteredBookings($filters);
                echo json_encode(["status" => "success", "bookings" => $result]);
            }
            break;

        case 'POST':
            if (!isset($inputData['reference'], $inputData['passenger_id'], $inputData['bus_id'], 
                      $inputData['reserve_name'], $inputData['passenger_type'], $inputData['seat_number'])) {
                echo json_encode(["status" => "error", "message" => "Missing required fields"]);
                break;
            }

            $result = $booking->createBooking(
                $inputData['reference'],
                $inputData['passenger_id'],
                $inputData['bus_id'],
                $inputData['reserve_name'],
                $inputData['passenger_type'],
                $inputData['seat_number'],
                $inputData['remarks'] ?? '',
                $inputData['status'] ?? 'pending'
            );

            echo json_encode($result ? 
                ["status" => "success", "message" => "Booking created successfully"] : 
                ["status" => "error", "message" => "Failed to create booking"]
            );
            break;

        case 'PUT':
            if (!isset($inputData['reference'], $inputData['passenger_type'], 
                      $inputData['seat_number'], $inputData['reserve_name'], $inputData['status'])) {
                echo json_encode(["status" => "error", "message" => "Missing required fields"]);
                break;
            }

            $result = $booking->updateBooking(
                $inputData['reference'],
                $inputData['reserve_name'],
                $inputData['passenger_type'],
                $inputData['seat_number'],
                $inputData['remarks'] ?? '',
                $inputData['status']
            );

            echo json_encode($result ? 
                ["status" => "success", "message" => "Booking updated successfully"] : 
                ["status" => "error", "message" => "Failed to update booking"]
            );
            break;

        case 'DELETE':
            if (!isset($_GET['reference'])) {
                echo json_encode(["status" => "error", "message" => "Reference is required"]);
                break;
            }

            $result = $booking->deleteBookingByReference($_GET['reference']);
            echo json_encode($result ? 
                ["status" => "success", "message" => "Booking deleted successfully"] : 
                ["status" => "error", "message" => "Failed to delete booking"]
            );
            break;

        default:
            echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
}
?>